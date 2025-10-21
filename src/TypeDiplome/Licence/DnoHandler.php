<?php

namespace App\TypeDiplome\Licence;

final class DnoHandler extends AbstractLicenceHandler
{
    protected function getLibelleCourt(): string
    {
        return 'DNO';
    }
}
