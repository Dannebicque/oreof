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

        /**
         * /semestres/1/ues/5/elementConstitutifs/0/elementConstitutif/mcccs/0/
         * /semestres/1/ues/5/elementConstitutifs/0/elementRaccroche/mcccs/0/
         * /semestres/1/ues/5/elementConstitutifs/0/mcccs/0/ (doublons avec le 1er?)
         * /semestres/1/ues/5/elementConstitutifs/1/heuresEctsEc/
         * /semestres/1/ues/5/heuresEctsUe/
         * /semestres/1/heuresEctsSemestre/"
         * /semestres/4/ues/4/uesEnfants/0/elementConstitutifs/1/heuresEctsEc/"
         * /semestres/4/ues/4/uesEnfants/0/heuresEctsUe/"
         * /semestres/4/ues/4/heuresEctsUeEnfants/1/"
         * /semestres/4/ues/5/elementConstitutifs/2/elementsConstitutifsEnfants/31941/elementConstitutif/ficheMatiere/"
         * /semestres/4/ues/5/elementConstitutifs/2/elementsConstitutifsEnfants/31941/heuresEctsEc/"
         * /semestres/4/ues/5/elementConstitutifs/2/heuresEctsEcEnfants/"
         * /semestres/6/ues/5/elementConstitutifs/1/elementConstitutif/natureUeEc/"
         * /heuresEctsFormation/"
         */

        $patterns = [
//            '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/elementConstitutif\/mcccs\/\d+\/',
//             '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/elementRaccroche\/mcccs\/\d+\/',
             '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/mcccs\/\d+\/', //(doublons avec le 1er?)
             '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/heuresEctsEc\/',
             '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/',
             '\/semestres\/\d+\/ues\/\d+\/heuresEctsUe\/',
             '\/semestres\/\d+\/heuresEctsSemestre\/',
             '\/semestres\/\d+\/ues\/\d+\/uesEnfants\/\d+\/elementConstitutifs\/\d+\/heuresEctsEc\/',
             '\/semestres\/\d+\/ues\/\d+\/uesEnfants\/\d+\/heuresEctsUe\/',
             '\/semestres\/\d+\/ues\/\d+\/heuresEctsUeEnfants\/\d+\/',
            '\/semestres\/\d+\/ues\/\d+\/uesEnfants\/\d+',
            '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/elementsConstitutifsEnfants\/\d+\/elementConstitutif\/ficheMatiere\/',
             '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/elementsConstitutifsEnfants\/\d+\/heuresEctsEc\/',
             '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/heuresEctsEcEnfants\/',
             '\/semestres\/\d+\/ues\/\d+\/elementConstitutifs\/\d+\/elementConstitutif\/natureUeEc\/',
             '\/heuresEctsFormation\/',
        ];

        $allparcours = $parcoursRepository->findByTypeValidationAttenteCfvu($this->getDpe(), 'soumis_central'); //soumis_cfvu

        foreach ($allparcours as $parcours) {
            //créer la sauvegarde JSON
            //récupère JSON CFVU
            $lastVersion = $parcoursVersioningRepository->findLastVersion($parcours);
            $lastVersion = count($lastVersion) > 0 ? $lastVersion[0] : null;

            //on fait une copie de la version courante en json
            $jsonCourant = $versioningParcours->saveVersionOfParcoursCourant($parcours);
            $jsonCfvu = $versioningParcours->loadJsonCfvu($parcours, $lastVersion);

            $comp = $parcours->getFormation()?->getComposantePorteuse();
            if ($comp !== null) {

                $tDemandes[$comp->getId()][$parcours->getId()] = [];

                $r = new JsonDiff(json_decode($jsonCfvu), json_decode($jsonCourant));
                $tDemandes[$comp->getId()][$parcours->getId()]['parcours'] = $parcours;
                $tDemandes[$comp->getId()][$parcours->getId()]['nbDiff'] = $r->getDiffCnt();

                $result['modified'] = [];
                $result['added'] = [];
                $result['removed'] = [];

                foreach ($r->getPatch()->jsonSerialize() as $patch) {
                    if (ExtractTextFromJsonPatch::getLastItem($patch->path)) {
                        $key = $this->extractPatternsFromString($patterns, $patch->path);
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
                                    $result['added'][$key['path']][$key['key']][] = ExtractTextFromJsonPatch::getAddFromPatch($patch);
                                    break;
                                case 'remove':
                                    $result['removed'][$key['path']][$key['key']][] = ExtractTextFromJsonPatch::getRemoveFromPatch($patch);
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

    private function extractPatternsFromString(array $patterns, string $string): ?array
    {
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
