<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Json/ExtractTextFromJsonPatch.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/06/2024 10:21
 */

namespace App\Classes\Json;

use Swaggest\JsonDiff\JsonDiff;
use Swaggest\JsonDiff\JsonPointer;

abstract class ExtractTextFromJsonPatch
{

    public static function getNewValueFromPatch($patch): string
    {
        if (!is_object($patch->value)) {
            return 'Nouvelle valeur pour "' . self::getField($patch->path) . '" : ' . $patch->value;
        }

        return 'Nouvelle valeur pour "' . self::getField($patch->path) . '" : valeur objet...';

    }

    public static function getOriginalValueFromPatch($patch): string
    {
        return $patch->value ?? 'pas de valeur';
    }

    public static function getTextFromPath($patch): string
    {
        return self::translatePath($patch->path);
    }

    public static function getAddFromPatch($patch)
    {
        return 'Element ajouté '. self::translatePath($patch->path).' ';
    }

    public static function getRemoveFromPatch($patch)
    {
        return 'Element supprimé : ' . self::translatePath($patch->path);
    }

    public static function translatePath($path): string
    {
        // partir de cette chaine pour construire une chaine texte compréhensible

        $tab = JsonPointer::splitPath($path);
        if (count($tab) > 2) {
            $texte = 'Semestre ' . $tab[1] . ', ';

            switch ($tab[2]) {
                case 'ues':
                    $texte .= 'UE ' . $tab[3] . ', ';
                    break;
            }

            return $texte;
        }

        return $path;
    }

    private static function getField($path)
    {

        $tab = JsonPointer::splitPath($path);
        if (count($tab) > 2) {
            $translateField = [
                'libelle' => 'Libellé',
                'cmPres' => 'Volume CM',
                'tdPres' => 'Volume TD',
                'tpPres' => 'Volume TP',
                'slug' => 'Slug',
                'sommeUeCmPres' => 'Total CM UE',
                'sommeUeTdPres' => 'Total TD UE',
                'sommeUeTpPres' => 'Total TP UE',
                'sommeUeEcts' => 'Total ECTS UE',
                'sommeSemestreCmPres' => 'Total CM Semestre',
                'sommeSemestreTdPres' => 'Total TD Semestre',
                'sommeSemestreTpPres' => 'Total TP Semestre',
                'sommeFormationCmPres' => 'Total CM Formation',
                'sommeFormationTdPres' => 'Total TD Formation',
                'sommeFormationTpPres' => 'Total TP Formation',
                'id' => 'id',
                'ects' => 'ECTS',
                'pourcentage' => 'Pourcentage',
                'controleContinu' => 'Contrôle Continu',
                'examenTerminal' => 'Examen Terminal',
                'numeroSession' => 'Numéro de session',
                'numeroEpreuve' => 'Numéro d\'épreuve',
                'typeMccc' => 'Type de MCCC',
                'libre' => 'Libre',
                'raccroche' => 'Raccroché',
                'nbEpreuves' => 'Nb épreuves',
                'code' => 'Code',
                'ordre' => 'Ordre',
                'sigle' => 'Sigle',
            ];
            return $translateField[$tab[count($tab) - 1]];
        }
        return 'field?';
    }

    public static function getLibelle(
        string $path,
        string  $jsonCourant
    ) {

        // todo: identifier les regex des cas possibles pour traiter ...Pourrait se faire avant sur le tableau de patch pour essayer de fusionner des lignes ?

        $tab = JsonPointer::splitPath($path);
        //selon la dernière valeur du tableau $tab, reconstruire un path avec les éléments précédents
        if (count($tab) > 2 &&
            ($tab[count($tab) - 1] === 'cmPres' ||
            $tab[count($tab) - 1] === 'tdPres' ||
            $tab[count($tab) - 1] === 'tpPres')
        ) {
            //retirer les 2 derniers éléments du tab
            array_pop($tab);
            array_pop($tab);

            if (count($tab) > 2 && $tab[count($tab) - 1] === 'heuresEctsEcEnfants') {
                //EC enfants
                array_pop($tab);
            }

            $elt = JsonPointer::get(json_decode($jsonCourant), $tab);
            if (!is_array($elt)) {
                return $elt->elementConstitutif->code;
            }
        }

        if (count($tab) > 4 && $tab[count($tab) - 3] === 'mcccs') {
            //EC enfants
            array_pop($tab);
            array_pop($tab);
            array_pop($tab);
            $elt = JsonPointer::get(json_decode($jsonCourant), $tab);

            if (!is_array($elt) && property_exists($elt, 'code')) {
                return $elt->code;
            }
        }

        return '';
    }

    public static function getLastItem(string $path): bool
    {
        $tab = JsonPointer::splitPath($path);

        if (count($tab) > 1 && ($tab[count($tab) - 1] === 'id' || $tab[count($tab) - 1] === 'slug')) {
            return false;
        }

        return true;

    }
}
