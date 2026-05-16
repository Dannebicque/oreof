<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/oreofv2/src/Form/UeMutualiserType.php
 * @author davidannebicque
 * @project oreofv2
 * @lastUpdate 16/05/2026 21:52
 */

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;

class UeMutualiserType extends MutualisationParcoursType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('controller_identifier', 'ue--mutualise');
    }
}

