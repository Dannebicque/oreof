<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/oreofv2/src/Twig/Components/_ui/Button.php
 * @author davidannebicque
 * @project oreofv2
 * @lastUpdate 03/05/2026 23:08
 */

namespace App\Twig\Components\UI;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent("Button", template: 'components/_ui/button.html.twig')]
final class Button
{
    /** primary | success | warning | danger | info | secondary */
    public string $variant = 'primary';

    /** sm | md | lg */
    public string $size = 'sm';

    /** outline (bordure seule) ou solid (fond coloré) */
    public bool $outline = false;

    /** soft : fond teinté léger + bordure claire (style par défaut) */
    public bool $soft = true;

    /** Nom de l'icône ux-icon (ex: icon:edit, ph:plus-bold) */
    public string $icon = '';

    /** Icône positionnée après le label */
    public bool $iconEnd = false;

    /** Texte du bouton */
    public string $label = '';

    /** Si renseigné, rend un <a>, sinon un <button> */
    public string $href = '';

    /** Attribut form (pour les boutons hors formulaire) */
    public string $form = '';

    /** Type du bouton : button | submit */
    public string $type = 'button';

    /** Titre tooltip */
    public string $tooltip = '';

    /** Désactivé */
    public bool $disabled = false;

    /** Classes CSS supplémentaires */
    public string $extraClass = '';
}
