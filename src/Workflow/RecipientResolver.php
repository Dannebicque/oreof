<?php

// src/Workflow/RecipientResolver.php
namespace App\Workflow;

use App\DTO\WorkFlowData;
use App\Entity\DpeParcours;
use App\Entity\Formation;
use App\Entity\Parcours;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class RecipientResolver
{
    public function __construct(
        private EntityManagerInterface        $em,
    )
    {
    }

    /**
     * @param object $subject (ex: DpeParcours)
     * @param array<string,mixed> $metadata Place/Transition metadata
     */
    public function resolveRecipients(array $metadata, WorkFlowData $workFlowData): array
    {
        $recipients = [];
        $copies = [];

        foreach ($metadata['recipients'] ?? [] as $role) {
            switch ($role) {
                case 'RF':
                    if (($resp = $workFlowData->formation?->getResponsableMention()) !== null) {
                        $this->em->initializeObject($resp);
                        $recipients[] = $resp;
                    }
                    break;
                case 'CoRF':
                    if (($resp = $workFlowData->formation?->getCoResponsable()) !== null) {
                        $this->em->initializeObject($resp);
                        $recipients[] = $resp;
                    }
                    break;
                case 'RP':
                    if (($resp = $workFlowData->parcours?->getRespParcours()) !== null) {
                        $this->em->initializeObject($resp);
                        $recipients[] = $resp;
                    }
                    break;
                case 'CoRP':
                    if (($resp = $workFlowData->parcours?->getCoResponsable()) !== null) {
                        $this->em->initializeObject($resp);
                        $recipients[] = $resp;
                    }
                    break;
                case 'DPE':
                    if (($resp = $workFlowData->composante?->getResponsableDpe()) !== null) {
                        $this->em->initializeObject($resp);
                        $recipients[] = $resp;
                    }
                    break;
            }
        }

        foreach ($metadata['recipientsCopy'] ?? [] as $copy) {
            if ($copy === 'SES') {
                $copies[] = WorkFlowData::EMAIL_OREOF;
            }
            if ($copy === 'CFVU') {
                $copies[] = WorkFlowData::EMAIL_CFVU;
            }
        }

        $recipients = array_values(array_unique($recipients, SORT_REGULAR));
        $copies = array_values(array_unique($copies, SORT_REGULAR));

        return ['recipients' => $recipients, 'copies' => $copies];
    }
}
