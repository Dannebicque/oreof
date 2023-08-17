<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/Components/NotificationsComponent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/03/2023 12:37
 */

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

    public function getNotifsNonLu(): int
    {
        return $this->notificationRepository->count(['destinataire' => $this->user, 'lu' => false]);
    }
}
