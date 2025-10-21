<?php

namespace App\TypeDiplome\Licence;

final class DfgHandler extends AbstractLicenceHandler
{
    protected function getLibelleCourt(): string
    {
        return 'DFG';
    }
}
