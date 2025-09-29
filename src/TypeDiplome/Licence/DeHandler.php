<?php

namespace App\TypeDiplome\Licence;

use App\TypeDiplome\TypeDiplomeHandlerInterface;

final class DeHandler extends AbstractLicenceHandler
{
    protected function getLibelleCourt(): string
    {
        return 'DE';
    }
}
