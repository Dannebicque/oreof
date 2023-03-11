<?php

namespace App\Twig\Components;

use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('notifications')]
final class NotificationsComponent extends AbstractController
{
    public ?UserInterface $user = null;

    public function __construct(private NotificationRepository $notificationRepository)
    {
    }

    public function getNotifs(): array
    {
        return $this->notificationRepository->findBy(['destinataire' => $this->user], ['created' => 'DESC']);
    }

    public function getNotifsNonLu(): int
    {
        return $this->notificationRepository->count(['destinataire' => $this->user, 'lu' => false]);
    }
}
