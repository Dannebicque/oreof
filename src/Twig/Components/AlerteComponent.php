<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/Components/AlerteComponent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/03/2023 23:10
 */

namespace App\Twig\Components;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('alerte')]
final class AlerteComponent extends AbstractController
{
    public string $type = 'info';
    public string $message = '';

    public function getIcone(): string
    {
        return match ($this->type) {
            'info' => 'icon:info:bold',
            'help' => 'icon:question:bold',
            'success' => 'icon:success:bold',
            'warning' => 'icon:warning:bold',
            'danger' => 'fican:danger:bold',
        };
    }
}
