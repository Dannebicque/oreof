<?php

namespace App\TypeDiplome\Licence;

final class DeHandler extends AbstractLicenceHandler
{
    protected function getLibelleCourt(): string
    {
        return 'DE';
    }
}
