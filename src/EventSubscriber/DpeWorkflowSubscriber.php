<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/DpeWorkflowSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 03/10/2025 12:58
 */

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use App\Notification\WorkflowNotifier;
use App\Workflow\RecipientResolver;

class DpeWorkflowSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private WorkflowNotifier  $notifier,
        private RecipientResolver $recipients
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.transition' => 'onTransition',
//            'workflow.dpeParcours.guard'     => 'onGuard',
        ];
    }

    public function onTransition(Event $event): void
    {
        $subject = $event->getSubject();
        $wf = $event->getWorkflow();
        $transition = $event->getTransition();
        if (null === $subject || null === $wf || null === $transition) {
            return;
        }
        $meta = $wf->getMetadataStore()->getTransitionMetadata($transition) ?? [];

        // Notifications "assignation"
        $eventKey = sprintf('workflow.dpeParcours.transition.%s', $transition->getName());
        $context = [
            'title' => $meta['label'] ?? $transition,
            'message' => $meta['message'] ?? 'Une nouvelle transition est assignée.',
            'subject' => $subject,
        ];
        $recipients = $this->recipients->resolveRecipients($subject, $meta);
        $this->notifier->notify($recipients, $eventKey, $wf->getName(), $context);
    }

//    public function onGuard(GuardEvent $event): void
//    {
//        $transition = $event->getTransition();
//        $wf = $event->getWorkflow();
//        $meta = $wf->getMetadataStore()->getTransitionMetadata($transition) ?? [];
//
//        // Exemple de guard générique : une propriété doit être remplie avant 'valider_*'
//        if (($meta['type'] ?? null) === 'valider') {
//            $subject = $event->getSubject();
//            if (method_exists($subject, 'isReadyForValidation') && !$subject->isReadyForValidation()) {
//                $event->setBlocked(true);
//                $event->addTransitionBlocker(new \Symfony\Component\Workflow\TransitionBlocker(
//                    "Des informations obligatoires manquent avant validation.", 'missing_data'
//                ));
//            }
//        }
//        // Tu peux aussi appliquer des guard par rôle/meta: $meta['required_role'] etc.
//    }
}
