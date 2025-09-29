<?php

namespace App\TypeDiplome\Licence;

final class DeustHandler extends AbstractLicenceHandler
{
    protected function getLibelleCourt(): string
    {
        return 'DEUST';
    }
}
