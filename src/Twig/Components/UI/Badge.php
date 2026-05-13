<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/oreofv2/src/Twig/Components/UI/Badge.php
 * @author davidannebicque
 * @project oreofv2
 * @lastUpdate 07/05/2026 21:05
 */

namespace App\Twig\Components\UI;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Badge', template: 'components/_ui/badge.html.twig')]
final class Badge
{
    /** primary | success | warning | danger | info | secondary */
    public string $variant = 'secondary';

    /** sm | md */
    public string $size = 'sm';

    /** soft = fond teinte / solid = fond plein */
    public bool $soft = true;

    /** rounded-full si true */
    public bool $pill = true;

    /** Libelle du badge */
    public string $label = '';

    /** Icone optionnelle (ux_icon) */
    public string $icon = '';

    /** Icone a droite du libelle */
    public bool $iconEnd = false;

    /** Classes CSS supplementaires */
    public string $extraClass = '';
}

