<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class HoraireTravail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ProfessionnelDeSante::class, inversedBy: 'horairesTravail')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProfessionnelDeSante $professionnel = null;

    #[ORM\Column(length: 10)]
    private string $jour;

    #[ORM\Column(type: 'time')]
    private \DateTimeInterface $heureDebut;

    #[ORM\Column(type: 'time')]
    private \DateTimeInterface $heureFin;

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

    public function getJour(): string
    {
        return $this->jour;
    }

    public function setJour(string $jour): self
    {
        $this->jour = $jour;

        return $this;
    }

    public function getHeureDebut(): \DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(\DateTimeInterface $heureDebut): self
    {
        $this->heureDebut = $heureDebut;

        return $this;
    }

    public function getHeureFin(): \DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin(\DateTimeInterface $heureFin): self
    {
        $this->heureFin = $heureFin;

        return $this;
    }
}