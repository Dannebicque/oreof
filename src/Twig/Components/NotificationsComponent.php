<?php

namespace App\Twig\Components;

use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('notifications')]
final class NotificationsComponent extends AbstractController
{
    public array $notifs = [];
    public ?UserInterface $user = null;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notifs = $notificationRepository->findBy(['destinataire' => $this->user], ['created' => 'DESC']);
    }
}
