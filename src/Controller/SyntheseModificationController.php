<?php

namespace App\Controller;

use App\Classes\Json\ExtractTextFromJsonPatch;
use App\Classes\MyGotenbergPdf;
use App\Repository\ComposanteRepository;
use App\Repository\ParcoursRepository;
use App\Repository\ParcoursVersioningRepository;
use App\Service\VersioningParcours;
use DateTime;
use Swaggest\JsonDiff\JsonDiff;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SyntheseModificationController extends BaseController
{
    #[Route('/synthese/modification/pdf', name: 'app_synthese_modification_export_pdf')]
    public function pdf(
        ParcoursVersioningRepository $parcoursVersioningRepository,
        ParcoursRepository   $parcoursRepository,
        VersioningParcours   $versioningParcours,
        ComposanteRepository $composanteRepository,
        MyGotenbergPdf       $myGotenbergPdf
    ): Response {
        $composantes = $composanteRepository->findAll();
        foreach ($composantes as $composante) {
            $tDemandes[$composante->getId()] = [];
        }

        $patterns = [
            '\/heuresEctsFormation\/',
             '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/heuresEctsEc\/',

             '\/semestres\/\d+\/heuresEctsSemestre\/',
            '\/semestres\/\d+\/ues\/\d+\/uesEnfants\/\d+',
            '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/elementsConstitutifsEnfants\/\d+\/elementConstitutif\/ficheMatiere\/',
             '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/elementsConstitutifsEnfants\/\d+\/heuresEctsEc\/',
             '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/elementConstitutif\/natureUeEc\/',
            '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/',
            '\/heuresEctsFormation\/',
        ];

        $patternsAIgnorer = [
            '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/mcccs\/\d+\/', //todo: temporairement le temps de gérer l'affichage propre des MCCC
            '\/semestres\/\d+\/ues\/\d+\/heuresEctsUe\/',
            '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/elementConstitutif\/mcccs\/\d+\/',
            '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/elementRaccroche\/mcccs\/\d+\/',
            '\/semestres\/\d+\/ues\/\d+\/uesEnfants\/\d+\/elementConstitutifs\/\d+\/heuresEctsEc\/',
            '\/semestres\/\d+\/ues\/\d+\/uesEnfants\/\d+\/heuresEctsUe\/',
            '\/semestres\/\d+\/ues\/\d+\/heuresEctsUeEnfants\/\d+\/',
            '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/heuresEctsEcEnfants\/',
        ];

        $allparcours = $parcoursRepository->findByTypeValidationAttenteCfvu($this->getDpe(), 'soumis_central'); //soumis_cfvu

        foreach ($allparcours as $parcours) {
            //créer la sauvegarde JSON
            //récupère JSON CFVU
            $lastVersion = $parcoursVersioningRepository->findLastVersion($parcours);
            $lastVersion = count($lastVersion) > 0 ? $lastVersion[0] : null;
            //dump($parcours->getLibelle());

            //on fait une copie de la version courante en json
            $jsonCourant = $versioningParcours->saveVersionOfParcoursCourant($parcours);
            $jsonCfvu = $versioningParcours->loadJsonCfvu($parcours, $lastVersion);

            $comp = $parcours->getFormation()?->getComposantePorteuse();
            if ($comp !== null) {

                $tDemandes[$comp->getId()][$parcours->getId()] = [];

                $r = new JsonDiff(json_decode($jsonCfvu), json_decode($jsonCourant));
                $tDemandes[$comp->getId()][$parcours->getId()]['parcours']['display'] = $parcours->getLibelle();
                $tDemandes[$comp->getId()][$parcours->getId()]['parcours']['formation'] = $parcours->getFormation()?->getDisplayLong();
                $tDemandes[$comp->getId()][$parcours->getId()]['nbDiff'] = $r->getDiffCnt();
                $result['modified'] = [];
                $result['added'] = [];
                $result['removed'] = [];

                foreach ($r->getPatch()->jsonSerialize() as $patch) {
                    if (ExtractTextFromJsonPatch::getLastItem($patch->path)) {
                        $key = $this->extractPatternsFromString($patterns, $patch->path, $patternsAIgnorer);
                        if ($key !== null) {
                            switch ($patch->op) {
                                case 'test':
                                    $result['modified'][$key['path']][$key['key']]['libelle'] = ExtractTextFromJsonPatch::getLibelle($patch->path, $jsonCourant);
                                    $result['modified'][$key['path']][$key['key']]['texte'] = ExtractTextFromJsonPatch::getTextFromPath($patch);
                                    $result['modified'][$key['path']][$key['key']]['original'] = ExtractTextFromJsonPatch::getOriginalValueFromPatch($patch);
                                    break;
                                case 'replace':
                                    $result['modified'][$key['path']][$key['key']]['nouveau'] = ExtractTextFromJsonPatch::getNewValueFromPatch($patch);
                                    break;
                                case 'add':
                                    $result['added'][$key['path']][$key['key']]['nouveau'] = ExtractTextFromJsonPatch::getLibelle($patch->path, $jsonCourant);
                                    $result['added'][$key['path']][$key['key']]['libelle'] = ExtractTextFromJsonPatch::getTextFromPath($patch);
                                    break;
                                case 'remove':
                                    $result['removed'][$key['path']][$key['key']]['origine'] = ExtractTextFromJsonPatch::getLibelle($patch->path, $jsonCfvu);
                                    $result['removed'][$key['path']][$key['key']]['libelle'] = ExtractTextFromJsonPatch::getTextFromPath($patch);
                                    break;
                            }
                        }
                    }
                }
                $tDemandes[$comp->getId()][$parcours->getId()]['patch'] = $result;
            }
        }

        return $myGotenbergPdf->render('pdf/synthese_modifications_parcours.html.twig', [
            'titre' => 'Liste des demandes de changement MCCC et maquettes',
            'demandes' => $tDemandes,
            'composantes' => $composantes,
            'dpe' => $this->getDpe(),
        ], 'synthese_changement_cfvu' . (new DateTime())->format('d-m-Y_H-i-s'));

    }

    private function extractPatternsFromString(array $patterns, string $string, array $patternsAIgnorer = []): ?array
    {
        // on regarde si c'est un pattern à ignorer
        foreach ($patternsAIgnorer as $pattern) {
            preg_match("/^(".$pattern.")/", $string, $matches);
            if (!empty($matches[0])) {
                return null;
            }
        }


        foreach ($patterns as $pattern) {
            preg_match("/^(".$pattern.")/", $string, $matches);
            if (!empty($matches[0])) {
                return ['path' => $matches[0],
                    'key' => substr($string, strlen($matches[0]))
                    ];
            }
        }

        return null;
    }
}
