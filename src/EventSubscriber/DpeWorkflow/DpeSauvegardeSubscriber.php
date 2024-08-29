<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/DpeWorkflow/DpeSauvegardeSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/07/2024 18:18
 */

namespace App\EventSubscriber\DpeWorkflow;

use App\Entity\DpeParcours;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\Tools;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Workflow\Event\Event;

class DpeSauvegardeSubscriber implements EventSubscriberInterface
{
    private string $dir;
    public function __construct(
        private TypeDiplomeRegistry $typeDiplomeRegistry,
        KernelInterface             $kernel,
        private readonly EntityManagerInterface $entityManager
    ) {
        $this->dir = $kernel->getProjectDir() . '/public/mccc/sauvegarde/';
    }

    public static function getSubscribedEvents(): array
    {
        return [
           // 'workflow.dpe.transition.valider_cfvu' => 'onValideCfvu',//todo: plus OK
            'workflow.dpeParcours.transition.valider_cfvu' => 'onValideCfvu',//todo: peut être en amont sur l'entrée
            //todo: ajouter sur réserve ?
        ];
    }

    public function onValideCfvu(Event $event): void
    {
        //todo:incrémenter version DPE ? ou créer une nouvelle ligne ? Histoire dans l'historique ? Sauvegarder le fichier Excel dans la table ?
        $dpeParcours = $event->getSubject();

        if ($dpeParcours instanceof DpeParcours) {
            $parcours = $dpeParcours->getParcours();
            $dpe = $dpeParcours->getCampagneCollecte();
            if (null === $parcours) {
                throw new \Exception('Parcours non trouvé');
            }
            $formation = $parcours->getFormation();

            if (null === $formation) {
                throw new \Exception('Pas de formation.');
            }
        } else {
            return ;
        }
        $typeDiplome = $this->typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome()?->getModeleMcc());

        if (null === $typeDiplome) {
            throw new \Exception('Aucun modèle MCC n\'est défini pour ce diplôme');
        }

        if (null === $dpe) {
            throw new \Exception('Aucune campagne de collecte n\'est définie pour ce diplôme');
        }


        $fichier = Tools::FileName('MCCC - ' . $dpe->getAnneeUniversitaire()?->getLibelle() . ' - ' . $parcours->getId().'-v'.$dpeParcours->getVersion(), 50);


        $export = $typeDiplome->exportExcelAndSaveVersionMccc(
            $dpe,
            $parcours,
            $this->dir,
            null,
            null,
            $fichier
        );

        if ($export !== false) {
            $dpeParcours->updateMinorVersion();
            $this->entityManager->flush();
        }
    }
}
