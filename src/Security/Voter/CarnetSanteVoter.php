<?php
// src/Security/Voter/CarnetSanteVoter.php

namespace App\Security\Voter;

use App\Entity\Patient;
use App\Entity\ProfessionnelDeSante;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CarnetSanteVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof Patient;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $patient = $subject;

        // L'admin a tous les droits
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($patient, $user);
            case self::EDIT:
                return $this->canEdit($patient, $user);
        }

        return false;
    }

    private function canView(Patient $patient, $user): bool
    {
        // Le patient peut toujours voir son propre carnet
        if ($user === $patient) {
            return true;
        }

        // Les professionnels autorisés peuvent voir si le carnet est partagé
        if ($user instanceof ProfessionnelDeSante) {
            return $patient->isCarnetPartage()
                && $patient->getProfessionnelsAutorisesCarnet()->contains($user);
        }

        return false;
    }

    private function canEdit(Patient $patient, $user): bool
    {
        // Seul le patient peut modifier les paramètres de son carnet
        return $user === $patient;
    }
}
