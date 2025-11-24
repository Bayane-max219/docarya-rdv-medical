<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Patient;
use App\Entity\ProfessionnelDeSante;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{

    // Ajout des constantes
    public const STATUT_EN_ATTENTE = 'en attente';
    public const STATUT_CONFIRME = 'confirmé';
    public const STATUT_ANNULE = 'annule';
    public const STATUT_TERMINE = 'terminé';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Patient $patient;

    #[ORM\ManyToOne(targetEntity: ProfessionnelDeSante::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?ProfessionnelDeSante $professionnel;

    #[ORM\Column(type: "datetime")]
    #[Assert\GreaterThan("now", message: "La date et heure du rendez-vous doivent être dans le futur.")]
    private ?\DateTimeInterface $dateHeure;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le statut ne peut pas être vide.")]
    #[Assert\Choice(choices: ["annule", "confirmé", "en attente", "terminé"], message: "Le statut doit être l'un des choix suivants : annule, confirmé, en attente.")]
    private ?string $statut;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le motif ne peut pas être vide.")]
    private ?string $motif;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $updatedAt;

    #[ORM\OneToOne(mappedBy: 'rendezVous', targetEntity: Consultation::class, cascade: ['persist', 'remove'])]
    private ?Consultation $consultation = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->statut = 'en attente'; // Statut par défaut
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;
        return $this;
    }

    public function getProfessionnel(): ?ProfessionnelDeSante
    {
        return $this->professionnel;
    }

    public function setProfessionnel(?ProfessionnelDeSante $professionnel): self
    {
        $this->professionnel = $professionnel;
        return $this;
    }

    public function getDateHeure(): ?\DateTimeInterface
    {
        return $this->dateHeure;
    }

    public function setDateHeure(?\DateTimeInterface $dateHeure): self
    {
        $this->dateHeure = $dateHeure;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    // Validation dans setStatut()
    public function setStatut(?string $statut): self
    {
        if (!in_array($statut, [self::STATUT_EN_ATTENTE, self::STATUT_CONFIRME, self::STATUT_ANNULE, self::STATUT_TERMINE])) {
            throw new \InvalidArgumentException("Statut invalide");
        }
        $this->statut = $statut;
        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): self
    {
        $this->motif = $motif;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getConsultation(): ?Consultation
    {
        return $this->consultation;
    }

    public function setConsultation(?Consultation $consultation): self
    {
        // unset the owning side of the relation if necessary
        if ($consultation === null && $this->consultation !== null) {
            $this->consultation->setRendezVous(null);
        }

        // set the owning side of the relation if necessary
        if ($consultation !== null && $consultation->getRendezVous() !== $this) {
            $consultation->setRendezVous($this);
        }

        $this->consultation = $consultation;
        return $this;
    }
}
