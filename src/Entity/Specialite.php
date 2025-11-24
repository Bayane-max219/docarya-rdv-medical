<?php

namespace App\Entity;

use App\Repository\SpecialiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpecialiteRepository::class)]
class Specialite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $categorie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sousCategorie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = 'active'; // Par défaut, 'active'

    #[ORM\OneToMany(mappedBy: 'specialite', targetEntity: ProfessionnelDeSante::class)]
    private Collection $professionnelsDeSante;

    public function __construct()
    {
        $this->professionnelsDeSante = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getSousCategorie(): ?string
    {
        return $this->sousCategorie;
    }

    public function setSousCategorie(?string $sousCategorie): static
    {
        $this->sousCategorie = $sousCategorie;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }
    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, ProfessionnelDeSante>
     */
    public function getProfessionnelsDeSante(): Collection
    {
        return $this->professionnelsDeSante;
    }

    public function addProfessionnelDeSante(ProfessionnelDeSante $professionnelDeSante): static
    {
        if (!$this->professionnelsDeSante->contains($professionnelDeSante)) {
            $this->professionnelsDeSante->add($professionnelDeSante);
            $professionnelDeSante->setSpecialite($this);
        }

        return $this;
    }

    public function removeProfessionnelDeSante(ProfessionnelDeSante $professionnelDeSante): static
    {
        if ($this->professionnelsDeSante->removeElement($professionnelDeSante)) {
            // set the owning side to null (unless already changed)
            if ($professionnelDeSante->getSpecialite() === $this) {
                $professionnelDeSante->setSpecialite(null);
            }
        }

        return $this;
    }
}
