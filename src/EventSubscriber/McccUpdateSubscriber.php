<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/McccUpdateSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/03/2025 21:39
 */

namespace App\EventSubscriber;

use App\Classes\GetDpeParcours;
use App\Classes\Mailer;
use App\Enums\TypeModificationDpeEnum;
use App\Events\McccUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class McccUpdateSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private Mailer $mailer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            McccUpdateEvent::UPDATE_MCCC => 'onMcccUpdate',
        ];
    }

    public function onMcccUpdate(McccUpdateEvent $event)
    {
        dump($event->getNewMcccToText());
        dump($event->getOldMcccToText());
        if ($event->getNewMcccToText() !== $event->getOldMcccToText()) {
            dump('MCCC modifié');
            // todo: réouvrir les parcours utilisant cet EC (fiche matière ou UE ou semestre si mutualisé)
            // mail au responsable de la formation/RP si réouverture
            $ficheMatiere = $event->getElementConstitutif()->getFicheMatiere();
            $ecs = $ficheMatiere->getElementConstitutifs();
            foreach ($ecs as $ec) {
                $parcours = $ec->getParcours();
                if ($parcours !== null) {
                    $dpeParcours = GetDpeParcours::getFromParcours($parcours);
                    if ($dpeParcours !== null && $dpeParcours->getEtatReconduction() === TypeModificationDpeEnum::OUVERT) {
                        $dpeParcours->setEtatReconduction(TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE);
                        $dpeParcours->setEtatValidation([1 => 'en_cours_redaction']);
                        //mail
                        $this->mailer->initEmail();
                        $this->mailer->setTemplate('mails/mutualisation/parcours_reouverture_mccc.html.twig', [
                            'parcours' => $parcours,
                            'ec' => $ec,
                            'ficheMatiere' => $ficheMatiere,
                            'oldMccc' => $event->getOldMcccToText(),
                            'newMccc' => $event->getNewMcccToText(),
                        ]);
                        $this->mailer->sendMessage(
                            [
                            $parcours->getRespParcours()?->getEmail(),
                            $parcours->getCoResponsable()?->getEmail(),
                            $parcours->getFormation()?->getResponsableMention()?->getEmail(),
                            $parcours->getFormation()?->getCoResponsable()?->getEmail(),
                    ],
                            'Réouverture des parcours pour modification des MCCC sur une fiche mutualisée'
                        );
                    }
                }
            }
        }
    }
}
