<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Notification/WorkflowNotifier.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 03/10/2025 12:56
 */

// src/Notification/WorkflowNotifier.php
namespace App\Notification;

use App\Entity\User;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Workflow\WorkflowInterface;

class WorkflowNotifier
{
    public function __construct(
        private MailerInterface                $mailer,
        private EntityManagerInterface         $em,
        private NotificationPreferenceResolver $preferenceResolver,
    )
    {
    }

    /**
     * @param string $eventKey ex: workflow.dpeParcours.entered.soumis_cfvu
     * @param array<string,mixed> $context (subject, transitionName, placeName, label, actor, comment, ...)
     */
    public function notify(array $recipients, string $eventKey, string $wf, array $context): void
    {
        foreach ($recipients as $user) {
            if (!$user instanceof User) {
                continue;
            }
            $pref = $this->preferenceResolver->resolveFor($user, $wf, $eventKey);

            // EMAIL
            if ($pref?->channelAllowed('email')) {
                //todo: fait un abstract pour récupérer toutes les datas pour le mail, reprendre existant du workflow
                $email = (new TemplatedEmail())
                    ->to($user->getEmail())
                    ->subject($context['subjectLine'] ?? ('Mise à jour — ' . $eventKey))
                    ->htmlTemplate(
                        file_exists(sprintf('%s/templates/mails/workflow/%s/%s.html.twig', $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2), $wf, $eventKey))
                            ? 'mails/workflow/' . $wf . '/' . $eventKey . '.html.twig'
                            : 'mails/workflow/default.html.twig'
                    )
                    ->context($context);
                $this->mailer->send($email);
            }

            // IN-APP
            if ($pref?->channelAllowed($eventKey, 'inapp')) {
                $n = new Notification();
                $n->setDestinataire($user);
                $n->setTitle($context['title'] ?? $eventKey);
                $n->setBody($context['message'] ?? null);
                $n->setPayload($context);
                $this->em->persist($n);
            }
        }
        $this->em->flush();
    }
}
