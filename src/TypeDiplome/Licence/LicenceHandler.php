<?php

namespace App\TypeDiplome\Licence;

final class LicenceHandler extends AbstractLicenceHandler
{
    protected function getLibelleCourt(): string
    {
        return 'L';
    }
}
