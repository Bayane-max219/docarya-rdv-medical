<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\ConsultationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConsultationRepository::class)]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: RendezVous::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?RendezVous $rendezVous = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotNull(message: "La date de la consultation ne peut pas être vide.")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: "json", nullable: true)]
    private array $ordonnances = [];

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: "Le prix de la consultation ne peut pas être vide.")]
    #[Assert\Positive(message: "Le prix doit être positif.")]
    private ?float $prix = null;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $partageAutorise = false;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $updatedAt;

    #[ORM\ManyToMany(targetEntity: ProfessionnelDeSante::class)]
    #[ORM\JoinTable(name: "consultation_partage_professionnels")]
    private Collection $professionnelsAutorises;

    public function __construct()
    {

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->professionnelsAutorises = new ArrayCollection();
    }

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRendezVous(): ?RendezVous
    {
        return $this->rendezVous;
    }

    public function setRendezVous(RendezVous $rendezVous): self
    {
        $this->rendezVous = $rendezVous;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getOrdonnances(): array
    {
        return $this->ordonnances;
    }

    public function setOrdonnances(array $ordonnances): self
    {
        $this->ordonnances = $ordonnances;
        return $this;
    }

    // Correction de addOrdonnance() si nécessaire
    public function addOrdonnance(array $ordonnance): self
    {
        // Si $ordonnance est un tableau à ajouter, utilisez :
        $this->ordonnances = array_merge($this->ordonnances, $ordonnance);
        // OU si c'est un objet simple, modifiez le paramètre
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function isPartageAutorise(): bool
    {
        return $this->partageAutorise;
    }

    public function setPartageAutorise(bool $partageAutorise): self
    {
        $this->partageAutorise = $partageAutorise;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
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
    public function getProfessionnelsAutorises(): Collection
    {
        return $this->professionnelsAutorises;
    }

    public function addProfessionnelAutorise(ProfessionnelDeSante $professionnel): self
    {
        if (!$this->professionnelsAutorises->contains($professionnel)) {
            $this->professionnelsAutorises->add($professionnel);
        }

        return $this;
    }

    public function removeProfessionnelAutorise(ProfessionnelDeSante $professionnel): self
    {
        $this->professionnelsAutorises->removeElement($professionnel);
        return $this;
    }

    public function isAccessibleBy(ProfessionnelDeSante $professionnel): bool
    {
        return $this->professionnelsAutorises->contains($professionnel);
    }
}
