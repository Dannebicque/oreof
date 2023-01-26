<?php

namespace App\Enums;

enum RegimeInscriptionEnum: string
{
    case FI = 'Formation Initiale';
    case FI_APPRENTISSAGE = 'Formation Initiale en apprentissage';
    case FC = 'Formation Continue';
    case FC_CONTRAT_PRO = 'Formation Continue Contrat Professionnalisation';
}
