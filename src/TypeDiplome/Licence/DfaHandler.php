<?php

namespace App\TypeDiplome\Licence;

final class DfaHandler extends AbstractLicenceHandler
{
    protected function getLibelleCourt(): string
    {
        return 'DFA';
    }
}
