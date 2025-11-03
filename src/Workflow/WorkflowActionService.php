<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Workflow/WorkflowActionService.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 03/10/2025 13:08
 */

namespace App\Workflow;

use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Exception\TransitionException;
use Symfony\Component\Security\Core\Security;
use App\Notification\WorkflowNotifier;
use App\Workflow\RecipientResolver;

class WorkflowActionService
{
    public function __construct(
        private Registry            $workflows,
        private StepHandlerRegistry $handlers,
        private WorkflowNotifier    $notifier,
        private RecipientResolver   $recipients,
        private Security            $security,
    )
    {
    }

    /**
     * @param object $subject (ex: DpeParcours)
     * @param string $workflowName (ex: 'dpeParcours')
     * @param string $transitionName (ex: 'valider_rf', 'refuser_rf', 'reserver_cfvu', …)
     * @param array<string,mixed> $data {comment, files[], date, …}
     */
    public function apply(object $subject, string $workflowName, string $transitionName, array $data = []): void
    {
        $wf = $this->workflows->get($subject, $workflowName);

        if (!$wf->can($subject, $transitionName)) {
            throw new \RuntimeException("Transition '$transitionName' impossible depuis l’état courant.");
        }

        // place courante (unique dans un 'workflow' non state_machine)
        $marking = $wf->getMarking($subject);
        $currentPlaces = array_keys($marking->getPlaces());
        $currentPlace = $currentPlaces[0] ?? null;

        // Lecture metadata de la transition (type, label, contraintes…)
        $transition = null;
        foreach ($wf->getDefinition()->getTransitions() as $t) {
            if ($t->getName() === $transitionName) {
                $transition = $t;
                break;
            }
        }
        $meta = $transition ? $wf->getMetadataStore()->getTransitionMetadata($transition) : [];

        // Contrôles déclaratifs génériques
        if (!empty($meta['requiredFields']) && \is_array($meta['requiredFields'])) {
            foreach ($meta['requiredFields'] as $field) {
                if (!\array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                    throw new \DomainException("Champ requis manquant: '$field'.");
                }
            }
        }
        if (!empty($meta['hasUpload']) && $meta['hasUpload'] === true && empty($data['files'])) {
            throw new \DomainException("Un ou plusieurs fichiers doivent être fournis.");
        }
        if (!empty($meta['hasDate']) && $meta['hasDate'] === true && empty($data['date'])) {
            throw new \DomainException("Une date doit être fournie.");
        }

        // Handler spécifique de l’étape courante (si présent)
        if ($handler = $this->handlers->get($currentPlace)) {
            $handler->validate($subject, $data);
            $handler->persist($subject, $data);
        }

        // Appliquer la transition
        $wf->apply($subject, $transitionName, $data);

        // Construire la clé d’événement & contexte
        $actor = $this->security->getUser();
        $toPlaces = array_keys($wf->getMarking($subject)->getPlaces());
        $toPlace = $toPlaces[0] ?? null;

        $eventKey = sprintf('workflow.%s.transition.%s', $workflowName, $transitionName);
        $context = [
            'title' => $meta['label'] ?? $transitionName,
            'message' => $meta['message'] ?? null,
            'subject' => $subject,
            'transition' => $transitionName,
            'from' => $currentPlace,
            'to' => $toPlace,
            'actor' => $actor,
            'comment' => $data['comment'] ?? null,
            'subjectLine' => sprintf('[%s] %s → %s', $workflowName, $currentPlace, $toPlace),
        ];

        // Destinataires en fonction des metadata de la transition (ou fallback à la place cible)
        $notifyMeta = $meta;
        if (!$this->hasNotifyHints($notifyMeta) && $toPlace) {
            $notifyMeta = array_merge(
                $wf->getMetadataStore()->getPlaceMetadata($toPlace) ?? [],
                $notifyMeta
            );
        }
        $recipients = $this->recipients->resolveRecipients($subject, $notifyMeta);
        $this->notifier->notify($recipients, $eventKey, $context);
    }

    private function hasNotifyHints(array $meta): bool
    {
        foreach (['notify_roles', 'notify_users', 'notify_responsable_property'] as $k) {
            if (!empty($meta[$k])) return true;
        }
        return false;
    }
}
