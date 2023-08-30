<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/verif/AbstractValide.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 28/08/2023 11:47
 */

namespace App\Classes\verif;

abstract class AbstractValide
{
    public const COMPLET = 'complet';
    public const INCOMPLET = 'incomplet';
    public const VIDE = 'vide';
    public const ERREUR = 'erreur';
    public const NON_CONCERNE = 'non_concerne';

    protected function nonVide(?string $field): string
    {
        if (null !== $field && '' !== $field) {
            return self::COMPLET;
        }

        return self::VIDE;
    }
}
