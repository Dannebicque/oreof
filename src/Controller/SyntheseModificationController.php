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

        $allparcours = $parcoursRepository->findByTypeValidationAttenteCfvu($this->getDpe(), 'soumis_central'); //soumis_cfvu

        foreach ($allparcours as $parcours) {
            //créer la sauvegarde JSON
            //récupère JSON CFVU
            $lastVersion = $parcoursVersioningRepository->findLastVersion($parcours);
            $lastVersion = count($lastVersion) > 0 ? $lastVersion[0] : null;

            //on fait une copie de la version courante en json
            $jsonCourant = $versioningParcours->saveVersionOfParcoursCourant($parcours);
            $jsonCfvu = $versioningParcours->loadJsonCfvu($parcours, $lastVersion);

            $comp = $parcours->getFormation()->getComposantePorteuse();
            $tDemandes[$comp->getId()][$parcours->getId()] = [];

            $r = new JsonDiff(json_decode($jsonCfvu), json_decode($jsonCourant));
            $tDemandes[$comp->getId()][$parcours->getId()]['parcours'] = $parcours;
            $tDemandes[$comp->getId()][$parcours->getId()]['nbDiff'] = $r->getDiffCnt();


            $tPatch = [];
            foreach ($r->getPatch()->jsonSerialize() as $patch) {
                switch ($patch->op) {
                    case 'test':
                        $tPatch['modification'][$patch->path]['libelle'] = ExtractTextFromJsonPatch::getLibelle($patch->path, $jsonCourant);
                        $tPatch['modification'][$patch->path]['texte'] = ExtractTextFromJsonPatch::getTextFromPath($patch);
                        $tPatch['modification'][$patch->path]['original'] = ExtractTextFromJsonPatch::getOriginalValueFromPatch($patch);
                        break;
                    case 'replace':
                        $tPatch['modification'][$patch->path]['nouveau'] = ExtractTextFromJsonPatch::getNewValueFromPatch($patch);
                        break;
                    case 'add':
                        $tPatch['add'][$patch->path] = ExtractTextFromJsonPatch::getAddFromPatch($patch);
                        break;
                    case 'remove':
                        $tPatch['remove'][$patch->path] = ExtractTextFromJsonPatch::getRemoveFromPatch($patch);
                        break;
                }
            }
            $tDemandes[$comp->getId()][$parcours->getId()]['patch'] = $tPatch;
        }

        return $myGotenbergPdf->render('pdf/synthese_modifications_parcours.html.twig', [
            'titre' => 'Liste des demandes de changement MCCC et maquettes',
            'demandes' => $tDemandes,
            'composantes' => $composantes,
            'dpe' => $this->getDpe(),
        ], 'synthese_changement_cfvu' . (new DateTime())->format('d-m-Y_H-i-s'));

    }
}
