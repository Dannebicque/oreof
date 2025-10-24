<?php

namespace App\TypeDiplome\Licence;

final class MasterHandler extends AbstractLicenceHandler
{
    protected function getLibelleCourt(): string
    {
        return 'M';
    }
}
