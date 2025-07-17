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
    public const string COMPLET = 'complet';
    public const string INCOMPLET = 'incomplet';
    public const string INCOMPLET_ECTS = 'incomplet_ects';
    public const string VIDE = 'vide';
    public const string ERREUR = 'erreur';
    public const string NON_CONCERNE = 'non_concerne';

    protected function nonVide(?string $field): string
    {
        if (null !== $field && '' !== $field) {
            return self::COMPLET;
        }

        return self::VIDE;
    }
}
