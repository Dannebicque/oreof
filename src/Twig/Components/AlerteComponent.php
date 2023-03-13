<?php

namespace App\Twig\Components;

use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('alerte')]
final class AlerteComponent extends AbstractController
{
    public string $type = 'info';
    public string $message = 'message';

    public function getIcone(): string
    {
        return match ($this->type) {
            'info' => 'fa-info-circle',
            'success' => 'fa-check-circle',
            'warning' => 'fa-exclamation-circle',
            'danger' => 'fa-times-circle',
        };
    }
}
