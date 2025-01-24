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
    public string $message = 'message';

    public function getIcone(): string
    {
        return match ($this->type) {
            'info' => 'fa-info-circle',
            'help' => 'fa-circle-question',
            'success' => 'fa-check-circle',
            'warning' => 'fa-exclamation-circle',
            'danger' => 'fa-times-circle',
        };
    }
}
