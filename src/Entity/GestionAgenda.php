<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class GestionAgenda
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ProfessionnelDeSante::class, inversedBy: 'indisponibilites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProfessionnelDeSante $professionnel = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $dateDebutIndispo;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $dateFinIndispo;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $motif = null;

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateDebutIndispo(): \DateTimeInterface
    {
        return $this->dateDebutIndispo;
    }

    public function setDateDebutIndispo(\DateTimeInterface $dateDebutIndispo): self
    {
        $this->dateDebutIndispo = $dateDebutIndispo;
        return $this;
    }

    public function getDateFinIndispo(): \DateTimeInterface
    {
        return $this->dateFinIndispo;
    }

    public function setDateFinIndispo(\DateTimeInterface $dateFinIndispo): self
    {
        $this->dateFinIndispo = $dateFinIndispo;
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
}
