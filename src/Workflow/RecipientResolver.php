<?php

// src/Workflow/RecipientResolver.php
namespace App\Workflow;

use App\Entity\Formation;
use App\Entity\Parcours;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class RecipientResolver
{
    private const MAIL_SES = 'oreof@univ-reims.fr';
    private const MAIL_CFVU = 'secretariat-cfvu@univ-reims.fr';

    public function __construct(
        private AuthorizationCheckerInterface $security,
        private EntityManagerInterface        $em,
    )
    {
    }

    /**
     * @param object $subject (ex: DpeParcours)
     * @param array<string,mixed> $metadata Place/Transition metadata
     * @return User[]
     */
    public function resolveRecipients(object $subject, array $metadata): array
    {
        $recipients = [];
        $copies = [];

        if ($subject instanceof Formation) {
            $formation = $subject;
            $parcours = null;
            $composante = $subject->getComposantePorteuse();
        } elseif ($subject instanceof Parcours) {
            $formation = $subject->getFormation();
            $parcours = $subject;
            $composante = $subject->getFormation()?->getComposantePorteuse();
        } else {
            return [];
        }

        foreach ($meta['recipients'] ?? [] as $role) {
            switch ($role) {
                case 'RF':
                    $recipients[] = $formation?->getResponsableMention();
                    break;
                case 'CoRF':
                    $recipients[] = $formation?->getCoResponsable();
                    break;
                case 'RP':
                    $recipients[] = $parcours?->getRespParcours();
                    break;
                case 'CoRP':
                    $recipients[] = $parcours?->getCoResponsable();
                    break;
                case 'DPE':
                    $recipients[] = $composante?->getResponsableDpe();
                    break;
            }
        }

        foreach ($meta['recipientsCopy'] ?? [] as $copy) {
            if ($copy === 'SES') {
                $copies[] = self::MAIL_SES;
            }
            if ($copy === 'CFVU') {
                $copies[] = self::MAIL_CFVU;
            }
        }

        $copies = array_values(array_unique($copies, SORT_REGULAR));
        $recipients = array_values(array_unique($recipients, SORT_REGULAR));

        return ['recipients' => $recipients, 'copies' => $copies];
    }
}
