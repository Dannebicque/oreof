<?php

namespace App\TypeDiplome\Licence;

final class LicenceProHandler extends AbstractLicenceHandler
{
    protected function getLibelleCourt(): string
    {
        return 'LP';
    }
}
