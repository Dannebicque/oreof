<?php

namespace App\Enums;

enum EtatDpeEnum : string
{
    /*
     *     - 'initialisation_dpe'
                - 'en_cours_redaction'
                - 'soumis_rf'
                - 'soumis_dpe_composante'
                - 'refuse_rf'
                - 'refuse_dpe_composante'
                - 'soumis_conseil'
                - 'refuse_conseil'
                - 'refuse_central'
                - 'soumis_central'
                - 'soumis_vp'
                - 'soumis_cfvu'
                - 'refuse_definitif_cfvu'
                - 'valie_a_publier'
                - 'publie'
                - 'valide_pour_publication'
                - 'soumis_conseil_reserve'
     */

    case initialisation_dpe = 'initialisation_dpe';
    case initialisation_ec = 'initialisation_ec';
    case en_cours_redaction = 'en_cours_redaction';
    case soumis_rf = 'soumis_rf';
    case soumis_ec = 'soumis_ec';
    case soumis_dpe_composante = 'soumis_dpe_composante';
    case refuse_rf = 'refuse_rf';
    case refuse_ec = 'refuse_ec';
    case refuse_dpe_composante = 'refuse_dpe_composante';
    case soumis_conseil = 'soumis_conseil';
    case refuse_conseil = 'refuse_conseil';
    case refuse_central = 'refuse_central';
    case soumis_central = 'soumis_central';
    case soumis_vp = 'soumis_vp';
    case soumis_cfvu = 'soumis_cfvu';
    case refuse_definitif_cfvu = 'refuse_definitif_cfvu';
    case valie_a_publier = 'valie_a_publier';
    case publie = 'publie';
    case valide_pour_publication = 'valide_pour_publication';
    case soumis_conseil_reserve = 'soumis_conseil_reserve';


    public function libelle(): string
    {
        return match($this) {
            self::initialisation_dpe => 'Initialisation DPE',
            self::initialisation_ec => 'Initialisation EC',
            self::en_cours_redaction => 'En cours de rédaction',
            self::soumis_rf => 'Soumis RF',
            self::soumis_ec => 'Soumis EC',
            self::soumis_dpe_composante => 'Soumis DPE composante',
            self::refuse_rf => 'Refusé RF',
            self::refuse_ec => 'Refusé EC',
            self::refuse_dpe_composante => 'Refusé DPE composante',
            self::soumis_conseil => 'Soumis Conseil',
            self::refuse_conseil => 'Refusé Conseil',
            self::refuse_central => 'Refusé Central',
            self::soumis_central => 'Soumis Central',
            self::soumis_vp => 'Soumis VP',
            self::soumis_cfvu => 'Soumis CFVU',
            self::refuse_definitif_cfvu => 'Refusé définitif CFVU',
            self::valie_a_publier => 'Validé à publier',
            self::publie => 'Publié',
            self::valide_pour_publication => 'Validé pour publication',
            self::soumis_conseil_reserve => 'Soumis Conseil réservé',
        };
    }
}
