<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Button
{
    public string $icon = '';
    public string $form = '';
    public string $submitLabel = '';
    public string $color = 'green';
    public string $href = '';
}
