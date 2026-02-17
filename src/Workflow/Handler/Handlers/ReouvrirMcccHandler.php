<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Workflow/Handler/Handlers/ReouvrirAvantPublieHandler.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 12/02/2026 21:03
 */

namespace App\Workflow\Handler\Handlers;

use App\DTO\Workflow\WorkflowTransitionMetaDto;
use App\Entity\DpeDemande;
use App\Entity\DpeParcours;
use App\Entity\User;
use App\Enums\EtatDpeEnum;
use App\Enums\TypeModificationDpeEnum;
use App\Workflow\Handler\AbstractDpeParcoursHandler;
use App\Workflow\Handler\TransitionHandlerInterface;

final class ReouvrirMcccHandler extends AbstractDpeParcoursHandler implements TransitionHandlerInterface
{
    public function supports(string $code): bool
    {
        return $code === 'reouvrir_mccc';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function handle(
        DpeParcours               $dpeParcours,
        User                      $user,
        WorkflowTransitionMetaDto $metaDto,
        string                    $transition,
        array                     $data
    ): void
    {
        // Récupération safe des champs (2–3 max => simple)
        $argumentaire = (string)($data['argumentaire'] ?? '');

        if ($argumentaire === '') {
            // Normalement déjà bloqué par required/NotBlank,
            // mais garder un garde-fou métier n’est jamais mauvais.
            throw new \DomainException("Argumentaire obligatoire.");
        }

        //créer le dpeDemande
        $parcours = $dpeParcours->getParcours();
        $formation = $parcours->getFormation();
        $campagneCollecte = $dpeParcours->getCampagneCollecte();
        $demande = new DpeDemande();
        $demande->setFormation($formation);
        $demande->setCampagneCollecte($campagneCollecte);
        $demande->setParcours($parcours);
        $demande->setAuteur($user);
        $demande->setEtatDemande(EtatDpeEnum::en_cours_redaction);
        $demande->setNiveauDemande('P'); // P Parcours (plus de log des demandes niveau formation?)
        $demande->setArgumentaireDemande($argumentaire);
        $demande->setNiveauModification(TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE);
        $dpeParcours->setEtatReconduction(TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE);
        $this->entityManager->persist($demande);
        $this->entityManager->flush();

        //appliquer la transition, gérer l'historique
        $this->dpeParcoursWorkflow->apply($dpeParcours, $transition, [
            'motif' => $argumentaire
        ]);
    }
}
