<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/EtatDpeEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Enums;

enum EtatDpeEnum: string
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
    case autorisation_saisie = 'autorisation_saisie';
    case en_cours_redaction = 'en_cours_redaction';
    case soumis_rf = 'soumis_rf';
    case soumis_parcours = 'soumis_parcours';
    case valide_parcours_rf = 'valide_parcours_rf';
    case reserve_parcours_rf = 'reserve_parcours_rf';
    case soumis_ec = 'soumis_ec';
    case soumis_dpe_composante = 'soumis_dpe_composante';
    case refuse_rf = 'refuse_rf';
    case transmis_rf = 'transmis_rf';
    case transmis_dpe = 'transmis_dpe';
    case refuse_ec = 'refuse_ec';
    case refuse_dpe_composante = 'refuse_dpe_composante';
    case soumis_conseil = 'soumis_conseil';
    case refuse_conseil = 'refuse_conseil';
    case refuse_central = 'refuse_central';
    case soumis_central = 'soumis_central';
    case soumis_vp = 'soumis_vp';
    case soumis_cfvu = 'soumis_cfvu';
    case refuse_definitif_cfvu = 'refuse_definitif_cfvu';
    case valide_a_publier = 'valide_a_publier';
    case publie = 'publie';
    case valide_pour_publication = 'valide_pour_publication';
    case soumis_conseil_reserve = 'soumis_conseil_reserve';
    case initialisation_parcours = 'initialisation_parcours';


    public function libelle(): string
    {
        return match ($this) {
            self::initialisation_dpe => 'Initialisation DPE',
            self::initialisation_ec => 'Initialisation EC',
            self::initialisation_parcours => 'Initialisation Parcours',
            self::autorisation_saisie => 'Saisie autorisée',
            self::en_cours_redaction => 'En cours de rédaction',
            self::soumis_rf => 'Soumis RF',
            self::transmis_rf => 'Transmis RF',
            self::transmis_dpe => 'Transmis DPE',
            self::soumis_ec => 'Soumis EC',
            self::soumis_dpe_composante => 'Soumis DPE composante',
            self::refuse_rf => 'Refusé RF',
            self::refuse_ec => 'Refusé EC',
            self::refuse_dpe_composante => 'Refusé DPE composante',
            self::soumis_conseil => 'Soumis Conseil',
            self::refuse_conseil => 'Refusé Conseil',
            self::refuse_central => 'Refusé Central',
            self::soumis_central => 'Soumis Central',
            self::soumis_parcours => 'Soumis Parcours',
            self::valide_parcours_rf => 'Valide Parcours Responsable de Formation',
            self::reserve_parcours_rf => 'Réserve Parcours Responsable de Formation',
            self::soumis_vp => 'Soumis VP',
            self::soumis_cfvu => 'Soumis CFVU',
            self::refuse_definitif_cfvu => 'Refusé définitif CFVU',
            self::valide_a_publier => 'Validé à publier',
            self::publie => 'Publié',
            self::valide_pour_publication => 'Validé CFVU', //todo: renommer en valide_cfvu
            self::soumis_conseil_reserve => 'Soumis Conseil réservé',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::initialisation_dpe, self::initialisation_ec, self::initialisation_parcours => 'secondary',
            self::autorisation_saisie, self::en_cours_redaction, self::reserve_parcours_rf => 'warning',
            self::soumis_rf, self::soumis_ec, self::soumis_dpe_composante, self::soumis_conseil, self::soumis_central, self::soumis_vp, self::soumis_cfvu, self::soumis_conseil_reserve, self::soumis_parcours, self::valide_parcours_rf => 'info',
            self::refuse_rf, self::refuse_ec, self::refuse_dpe_composante, self::refuse_conseil, self::refuse_central, self::refuse_definitif_cfvu => 'danger',
            self::valide_a_publier, self::publie, self::valide_pour_publication, self::transmis_rf, self::transmis_dpe  => 'success',
        };
    }
}
