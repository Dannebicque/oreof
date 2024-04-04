<?php

namespace App\Controller;

use App\Classes\Excel\ExcelWriter;
use App\Classes\ValidationProcess;
use App\Classes\ValidationProcessFicheMatiere;
use App\Entity\Composante;
use App\Repository\ComposanteRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\FormationRepository;

use App\Utils\Tools;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ValidationController extends BaseController
{
    #[Route('/validation', name: 'app_validation_index')]
    public function index(
        Request              $request,
    ): Response {
        return $this->render('validation/index.html.twig', [
            'step' => $request->query->get('step', 'formation'),
        ]);
    }

    #[Route('/validation/fiche/export', name: 'app_validation_verification_fiche_export')]
    public function ficheExportVerification(
        ExcelWriter $excelWriter,
        FicheMatiereRepository $ficheMatiereRepository,
    ): Response {
        $fiches[] = $ficheMatiereRepository->findByTypeValidation($this->getDpe(), 'fiche_matiere');
        $fiches[] = $ficheMatiereRepository->findByTypeValidationNull($this->getDpe());
        $fiches = array_merge(...$fiches);
        $excelWriter->nouveauFichier('Export Vérification');
        $excelWriter->setActiveSheetIndex(0);

        $excelWriter->writeCellXY(1, 1, 'Composante');
        $excelWriter->writeCellXY(2, 1, 'Type Diplôme');
        $excelWriter->writeCellXY(3, 1, 'Mention');
        $excelWriter->writeCellXY(4, 1, 'Parcours');
        $excelWriter->writeCellXY(5, 1, 'Fiche');
        $excelWriter->writeCellXY(6, 1, 'Responsable');

        $ligne = 2;

        foreach ($fiches as $fiche) {
            if ($fiche->getParcours() !== null && $fiche->getElementConstitutifs()->count() > 0) {

                $parcours = $fiche->getParcours();
                $formation = $parcours?->getFormation();
                $composante = $formation?->getComposantePorteuse();
                $responsable = $fiche->getResponsableFicheMatiere();

                $excelWriter->writeCellXY(1, $ligne, $composante ? $composante?->getLibelle() : 'Pas de composante');
                $excelWriter->writeCellXY(2, $ligne, $formation ? $formation?->getTypeDiplome()?->getLibelle() : 'Pas de formation');
                $excelWriter->writeCellXY(3, $ligne, $formation ? $formation?->getDisplay() : 'Pas de formation');
                $excelWriter->writeCellXY(4, $ligne, $parcours ? $parcours->getLibelle() : 'Pas de parcours');
                $excelWriter->writeCellXY(5, $ligne, $fiche->getLibelle());
                $excelWriter->writeCellXY(6, $ligne, $responsable ? $responsable->getNom() . ' ' . $responsable->getPrenom() : 'Pas de responsable');

                $ligne++;
            }
        }


        $fileName = Tools::FileName('Verif-fiche-'. (new DateTime())->format('d-m-Y-H-i'), 50);
        return $excelWriter->genereFichier($fileName, true);
    }

    #[Route('/validation/wizard', name: 'app_validation_wizard')]
    public function wizard(
        Request $request,
        ValidationProcessFicheMatiere    $validationProcessFicheMatiere,
        ValidationProcess    $validationProcess,
        ComposanteRepository $composanteRepository,
    ): Response {
        //todo:  Affichage des bons boutons selon le process et le status choisi sur la liste en bas et haut de page.
        //todo: filtrer si DPE composante ou pas
        $idComposante = $request->query->get('composante', null);
        $step = $request->query->get('step', 'formation');
        $composante = null;
        $isSes = $this->isGranted('ROLE_SES');

        if (!$isSes && $idComposante !== null) {
            $composante = $composanteRepository->find($idComposante);
        }

        switch ($step) {
            case 'formation':
                return $this->render('validation/_formation.html.twig', [
                    'ses' => $isSes,
                    'composante' => $composante,
                    'composantes' => $composanteRepository->findAll(),
                    'types_validation' => $validationProcess->getProcess(),
                ]);
            case 'fiche':
                return $this->render('validation/_fiches.html.twig', [
                    'ses' => $isSes,
                    'composante' => $composante,
                    'composantes' => $composanteRepository->findAll(),
                    'types_validation' => $validationProcessFicheMatiere->getProcess(),
                ]);
        }
    }



    #[Route('/validation/composante/{composante}', name: 'app_validation_composante_index')]
    public function composante(
        Request              $request,
        ComposanteRepository $composanteRepository,
        Composante           $composante
    ): Response {
        $step = $request->query->get('step', 'formation');

        return $this->render('validation/index.html.twig', [
            'step' => $step,
            'composante' => $composante->getId(),
            'composantes' => $composanteRepository->findAll(),

        ]);
    }

    #[Route('/validation/liste', name: 'app_validation_formation_liste')]
    public function liste(
        ValidationProcess    $validationProcess,
        ComposanteRepository $composanteRepository,
        FormationRepository  $formationRepository,
        Request              $request
    ): Response {
        $typeValidation = $request->query->get('typeValidation');
        $process = $validationProcess->getEtape($typeValidation);

        if ($request->query->has('composante')) {
            if ($request->query->get('composante') === 'all') {
                $composante = null;
                $formations = $formationRepository->findByTypeValidation($this->getDpe(), $process['transition']);
            } else {
                $composante = $composanteRepository->find($request->query->get('composante'));
                if (!$composante) {
                    throw $this->createNotFoundException('La composante n\'existe pas');
                }
                $formations = $formationRepository->findByComposanteTypeValidation($composante, $this->getDpe(), $process['transition']);
            }




        } else {
            $formations = [];
            $process = null;
        }

        $composantes = $composanteRepository->findAll();
        return $this->render('validation/_liste.html.twig', [
            'process' => $process,
            'formations' => $formations,
            'composantes' => $composantes,
            'etape' => $typeValidation ?? null,
        ]);
    }

    #[Route('/validation/liste/fiche', name: 'app_validation_formation_liste_fiche')]
    public function listeFiche(
        ValidationProcessFicheMatiere    $validationProcessFicheMatiere,
        ComposanteRepository $composanteRepository,
        FicheMatiereRepository  $ficheMatiereRepository,
        Request              $request
    ): Response {
        $typeValidation = $request->query->get('typeValidation');
        $process = $validationProcessFicheMatiere->getEtape($typeValidation);

        if ($request->query->has('composante')) {
            if ($request->query->get('composante') === 'all') {
                $fiches = $ficheMatiereRepository->findByTypeValidation($this->getDpe(), $typeValidation);
            } else {
                $composante = $composanteRepository->find($request->query->get('composante'));
                if (!$composante) {
                    throw $this->createNotFoundException('La composante n\'existe pas');
                }
                $fiches = $ficheMatiereRepository->findByComposanteTypeValidation($composante, $this->getDpe(), $typeValidation);
            }
        } else {
            $fiches = [];
            $process = null;
        }

        $composantes = $composanteRepository->findAll();
        return $this->render('validation/_listeFiches.html.twig', [
            'process' => $process,
            'fiches' => $fiches,
            'composantes' => $composantes,
            'etape' => $typeValidation ?? null,
        ]);
    }
}
