<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: PatientRepository::class)]
class Patient extends User
{
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $antecedentsMedicaux = null;

    #[ORM\Column(type: Types::JSON)]
    private array $maladiesChroniques = [];

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $carnetPartage = false;

    #[ORM\ManyToMany(targetEntity: ProfessionnelDeSante::class)]
    #[ORM\JoinTable(name: 'patient_partage_carnet')]
    private Collection $professionnelsAutorisesCarnet;

    public function __construct()
    {
        $this->professionnelsAutorisesCarnet = new ArrayCollection();
    }

    public function getAntecedentsMedicaux(): ?string
    {
        return $this->antecedentsMedicaux;
    }

    public function setAntecedentsMedicaux(?string $antecedentsMedicaux): static
    {
        $this->antecedentsMedicaux = $antecedentsMedicaux;

        return $this;
    }

    public function getMaladiesChroniques(): array
    {
        return $this->maladiesChroniques;
    }

    public function setMaladiesChroniques(array $maladiesChroniques): static
    {
        $this->maladiesChroniques = $maladiesChroniques;

        return $this;
    }

    // Getters et Setters
    public function isCarnetPartage(): bool
    {
        return $this->carnetPartage;
    }

    public function setCarnetPartage(bool $carnetPartage): self
    {
        $this->carnetPartage = $carnetPartage;
        return $this;
    }

    public function getProfessionnelsAutorisesCarnet(): Collection
    {
        return $this->professionnelsAutorisesCarnet;
    }

    public function addProfessionnelAutoriseCarnet(ProfessionnelDeSante $professionnel): self
    {
        if (!$this->professionnelsAutorisesCarnet->contains($professionnel)) {
            $this->professionnelsAutorisesCarnet->add($professionnel);
        }
        return $this;
    }

    public function removeProfessionnelAutoriseCarnet(ProfessionnelDeSante $professionnel): self
    {
        $this->professionnelsAutorisesCarnet->removeElement($professionnel);
        return $this;
    }
}
