<?php

namespace App\TypeDiplome\Licence;

final class MeefHandler extends AbstractLicenceHandler
{
    protected function getLibelleCourt(): string
    {
        return 'MEEF';
    }
}
