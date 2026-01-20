<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/Components/DropdownActionsComponent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/01/2026 08:33
 */

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'dropdown_actions')]
class DropdownActionsComponent
{
    /**
     * @var array<int, array{
     *   label: string,
     *   url?: string,
     *   icon?: string,
     *   method?: 'GET'|'POST'|'DELETE',
     *   turboFrame?: string,
     *   confirm?: string,
     *   danger?: bool,
     *   disabled?: bool,
     *   csrf?: string
     * }>
     */
    public array $items = [];

    /** Texte SR pour le bouton */
    public string $label = 'Actions';

    /** Permet de distinguer plusieurs dropdown sur une page (facultatif) */
    public ?string $id = null;
}
