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
        if ($event->hasDiff()) {
            // mail au responsable de la formation/RP si réouverture
            $ficheMatiere = $event->getElementConstitutif()->getFicheMatiere();
            if ($ficheMatiere !== null) {
                $ecs = $ficheMatiere->getElementConstitutifs();
                foreach ($ecs as $ec) {
                    $parcours = $ec->getParcours();
                    if ($parcours !== null && $parcours->getId() !== $event->getParcours()->getId()) {
                        //mail
                        $this->mailer->initEmail();
                        $this->mailer->setTemplate('mails/mutualisation/parcours_reouverture_mccc.html.twig', [
                            'parcours' => $parcours,
                            'formation' => $parcours->getFormation(),
                            'ec' => $ec,
                            'ficheMatiere' => $ficheMatiere
                        ]);
                        $this->mailer->sendMessage(
                            [
                                $parcours->getRespParcours()?->getEmail(),
                                $parcours->getCoResponsable()?->getEmail(),
                                $parcours->getFormation()?->getResponsableMention()?->getEmail(),
                                $parcours->getFormation()?->getCoResponsable()?->getEmail(),
                            ],
                            '[ORéOF] Un élément mutualisé avec l\'un de vos parcours a été modifié'
                        );
                    }
                }
            }
        }
    }
}
