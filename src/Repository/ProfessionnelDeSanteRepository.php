<?php

namespace App\Repository;

use App\Entity\ProfessionnelDeSante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class ProfessionnelDeSanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfessionnelDeSante::class);
    }

    /**
     * Recherche avancée de professionnels avec filtres et géolocalisation
     */
    public function findByAdvancedFilters(array $filters, int $page = 1, int $limit = 10): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.specialite', 's')
            ->select('p');

        // Filtres sur la spécialité
        if (!empty($filters['specialite_nom'])) {
            $qb->andWhere('s.nom LIKE :specialite_nom OR p.nom LIKE :specialite_nom OR p.prenom LIKE :specialite_nom')
                ->setParameter('specialite_nom', '%' . $filters['specialite_nom'] . '%');
        }

        if (!empty($filters['categorie'])) {
            $qb->andWhere('s.categorie = :categorie')
                ->setParameter('categorie', $filters['categorie']);
        }

        if (!empty($filters['sous_categorie'])) {
            $qb->andWhere('s.sousCategorie = :sous_categorie')
                ->setParameter('sous_categorie', $filters['sous_categorie']);
        }

        if (!empty($filters['specialite_select'])) {
            $qb->andWhere('s.nom = :specialite_select')
                ->setParameter('specialite_select', $filters['specialite_select']);
        }

        // Filtres géographiques
        if (!empty($filters['geolocalisation'])) {
            $qb->andWhere('p.adresse LIKE :geolocalisation OR p.ville LIKE :geolocalisation OR p.codePostal LIKE :geolocalisation')
                ->setParameter('geolocalisation', '%' . $filters['geolocalisation'] . '%');
        }

        // Filtres de géolocalisation avancés
        if (!empty($filters['user_latitude']) && !empty($filters['user_longitude'])) {
            $earthRadius = 6371; // km
            $userLat = (float)$filters['user_latitude'];
            $userLng = (float)$filters['user_longitude'];
            $radius = (float)$filters['search_radius'];

            // Formule Haversine pour calculer la distance
            $qb->addSelect(sprintf(
                '(%f * ACOS(COS(RADIANS(%f)) * COS(RADIANS(p.latitude)) * COS(RADIANS(p.longitude) - RADIANS(%f)) + SIN(RADIANS(%f)) * SIN(RADIANS(p.latitude)))) AS HIDDEN distance',
                $earthRadius,
                $userLat,
                $userLng,
                $userLat
            ))
                ->andWhere('p.latitude IS NOT NULL AND p.longitude IS NOT NULL')
                ->andWhere(sprintf(
                    '(%f * ACOS(COS(RADIANS(%f)) * COS(RADIANS(p.latitude)) * COS(RADIANS(p.longitude) - RADIANS(%f)) + SIN(RADIANS(%f)) * SIN(RADIANS(p.latitude)))) <= %f',
                    $earthRadius,
                    $userLat,
                    $userLng,
                    $userLat,
                    $radius
                ))
                ->orderBy('distance', 'ASC'); // Tri par distance croissante
        }

        // Filtres sur le tarif
        if (!empty($filters['tarif_min'])) {
            $qb->andWhere('p.tarif >= :tarif_min')
                ->setParameter('tarif_min', $filters['tarif_min']);
        }

        if (!empty($filters['tarif_max'])) {
            $qb->andWhere('p.tarif <= :tarif_max')
                ->setParameter('tarif_max', $filters['tarif_max']);
        }

        // Filtres sur les horaires de travail
        if (!empty($filters['jour']) || !empty($filters['heure_debut']) || !empty($filters['heure_fin'])) {
            $qb->leftJoin('p.horairesTravail', 'h');

            if (!empty($filters['jour'])) {
                $qb->andWhere('h.jour = :jour')
                    ->setParameter('jour', $filters['jour']);
            }

            if (!empty($filters['heure_debut'])) {
                $qb->andWhere('h.heureDebut <= :heure_debut')
                    ->setParameter('heure_debut', $filters['heure_debut']);
            }

            if (!empty($filters['heure_fin'])) {
                $qb->andWhere('h.heureFin >= :heure_fin')
                    ->setParameter('heure_fin', $filters['heure_fin']);
            }
        }

        // Pagination
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb->getQuery());
    }

    /**
     * Trouve les professionnels dans un rayon donné autour de coordonnées GPS
     * avec tri par distance
     */
    public function findNearbyProfessionals(float $latitude, float $longitude, float $radius, int $maxResults = 10): array
    {
        $earthRadius = 6371; // km

        return $this->createQueryBuilder('p')
            ->addSelect(sprintf(
                '(%f * ACOS(COS(RADIANS(%f)) * COS(RADIANS(p.latitude)) * COS(RADIANS(p.longitude) - RADIANS(%f)) + SIN(RADIANS(%f)) * SIN(RADIANS(p.latitude)))) AS HIDDEN distance',
                $earthRadius,
                $latitude,
                $longitude,
                $latitude
            ))
            ->andWhere('p.latitude IS NOT NULL AND p.longitude IS NOT NULL')
            ->andHaving('distance <= :radius')
            ->setParameter('radius', $radius)
            ->orderBy('distance', 'ASC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }

    public function findAllSpecialitesWithDetails(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.specialite', 's')
            ->select('s.nom', 's.categorie', 's.sousCategorie', 's.code', 's.statut')
            ->distinct(true);

        $result = $qb->getQuery()->getResult();

        $specialites = [];
        foreach ($result as $row) {
            $specialites[] = [
                'nom' => $row['nom'],
                'categorie' => $row['categorie'],
                'sousCategorie' => $row['sousCategorie'],
                'code' => $row['code'],
                'statut' => $row['statut'],
            ];
        }

        return $specialites;
    }

    public function findDistinctCategories(): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT s.categorie')
            ->join('p.specialite', 's')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findDistinctSousCategories(string $categorie): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT s.sousCategorie')
            ->join('p.specialite', 's')
            ->where('s.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findSpecialitesByCategoryAndSubcategory(string $categorie, string $sousCategorie): array
    {
        return $this->createQueryBuilder('p')
            ->select('s.nom')
            ->join('p.specialite', 's')
            ->where('s.categorie = :categorie')
            ->andWhere('s.sousCategorie = :sousCategorie')
            ->setParameter('categorie', $categorie)
            ->setParameter('sousCategorie', $sousCategorie)
            ->groupBy('s.nom')
            ->getQuery()
            ->getSingleColumnResult();
    }
}
