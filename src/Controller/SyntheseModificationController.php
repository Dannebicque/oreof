<?php

namespace App\Controller;

use App\Classes\MyGotenbergPdf;
use App\Repository\ComposanteRepository;
use App\Repository\DpeParcoursRepository;
use App\Repository\ParcoursRepository;
use App\Service\VersioningParcours;
use App\Service\VersioningStructure;
use App\TypeDiplome\TypeDiplomeRegistry;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SyntheseModificationController extends BaseController
{
    #[Route('/synthese/modification/pdf', name: 'app_synthese_modification_export_pdf')]
    public function pdf(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        ParcoursRepository $parcoursRepository,
        ComposanteRepository $composanteRepository,
        VersioningParcours $versioningParcours,
        MyGotenbergPdf $myGotenbergPdf
    ): Response {
        $composantes = $composanteRepository->findAll();
        $tDemandes = [];

        foreach ($composantes as $composante) {
            $tDemandes[$composante->getId()] = [];
        }

        //récupérer uniquement les DPE ouverts
        $parcours = $parcoursRepository->findByTypeValidationAttenteCfvu($this->getDpe(), 'soumis_central'); //soumis_cfvu

        foreach ($parcours as $parc) {
//            if ($parc->getId() === 405) {
                $composante = $parc->getFormation()?->getComposantePorteuse();
                if (null !== $composante) {
                    $typeD = $typeDiplomeRegistry->getTypeDiplome($parc->getFormation()?->getTypeDiplome()?->getModeleMcc());
                    // récupérer les demandes de changement et de modification
                    $dto = $typeD->calculStructureParcours($parc, true, false);
                    $structureDifferencesParcours = $versioningParcours->getStructureDifferencesBetweenParcoursAndLastVersion($parc);
                    //                if ($parc->getId() === 405) {
                    //                   dd($structureDifferencesParcours);
                    //                }
                    if ($structureDifferencesParcours !== null) {
                        $diffStructure = (new VersioningStructure($structureDifferencesParcours, $dto))->calculDiff();
                    } else {
                        $diffStructure = null;
                    }
//dd($diffStructure);
                    $tDemandes[$composante->getId()][] = ['parcours' => $parc, 'diffStructure' => $diffStructure, 'dto' => $dto];
                }
            //}
        }

        return $myGotenbergPdf->render('pdf/synthese_modifications.html.twig', [
            'titre' => 'Liste des demande de changement MCCC et maquettes',
            'demandes' => $tDemandes,
            'composantes' => $composantes,
            'dpe' => $this->getDpe(),
        ], 'synthese_changement_cfvu'.(new DateTime())->format('d-m-Y_H-i-s'));

    }
}
