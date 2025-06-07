<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/ValidationComposanteController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/05/2025 16:32
 */

namespace App\Controller;

use App\Classes\Excel\ExcelWriter;
use App\Classes\ValidationProcess;
use App\Classes\ValidationProcessChangeRf;
use App\Classes\ValidationProcessFicheMatiere;
use App\Entity\Composante;
use App\Enums\TypeModificationDpeEnum;
use App\Repository\ChangeRfRepository;
use App\Repository\ComposanteRepository;
use App\Repository\DpeParcoursRepository;
use App\Repository\FicheMatiereRepository;
use App\Utils\Tools;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/validation/composante/', name: 'app_validation_composante_')]
class ValidationComposanteController extends BaseController
{

    #[Route('/validation/fiche/export', name: 'app_validation_verification_fiche_export')]
    //todo: gérer la composante
    public function ficheExportVerification(
        ExcelWriter            $excelWriter,
        FicheMatiereRepository $ficheMatiereRepository,
    ): Response
    {
        $fiches[] = $ficheMatiereRepository->findByTypeValidation($this->getCampagneCollecte(), 'fiche_matiere');
        $fiches[] = $ficheMatiereRepository->findByTypeValidationNull($this->getCampagneCollecte());
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


        $fileName = Tools::FileName('Verif-fiche-' . (new DateTime())->format('d-m-Y-H-i'), 50);
        return $excelWriter->genereFichier($fileName, true);
    }

    #[Route('{composante}/dpe', name: 'dpe_index')]
    public function composante(
        Request           $request,
        Composante        $composante,
        ValidationProcess $validationProcess,
    ): Response
    {

        $typeValidation = $request->query->get('typeValidation');

        return $this->render('validation-composante/dpe.html.twig', [
            'composante' => $composante,
            'types_validation' => $validationProcess->getProcessAll(),
            'typeValidation' => $typeValidation,
        ]);
    }

    #[Route('{composante}/change-rf', name: 'change_rf_index')]
    public function changeRf(
        Request                   $request,
        Composante                $composante,
        ValidationProcessChangeRf $validationProcessChangeRf,
    ): Response
    {
        $typeValidation = $request->query->get('typeValidation');

        return $this->render('validation-composante/change_rf.html.twig', [
            'composante' => $composante,
            'types_validation' => $validationProcessChangeRf->getProcess(),//faire un getProcesssComposante pour filtrer par niveau composante,
            'typeValidation' => $typeValidation,

        ]);
    }

    #[Route('{composante}/fiche-matiere', name: 'fiche_index')]
    public function ficheMatiere(
        Request                       $request,
        Composante                    $composante,
        ValidationProcessFicheMatiere $validationProcessFicheMatiere,
    ): Response
    {
        $typeValidation = $request->query->get('typeValidation');

        return $this->render('validation-composante/fiche_matiere.html.twig', [
            'composante' => $composante,
            'types_validation' => $validationProcessFicheMatiere->getProcess(),
            'typeValidation' => $typeValidation,

        ]);
    }

    #[Route('{composante}/dpe/liste', name: 'dpe_liste')]
    public function dpeListe(
        ValidationProcess     $validationProcess,
        DpeParcoursRepository $dpeParcoursRepository,
        Composante            $composante,
        Request               $request
    ): Response
    {
        $typeValidation = $request->query->get('typeValidation');
        $process = $validationProcess->getEtape($typeValidation);

        if (!$composante) {
            throw $this->createNotFoundException('La composante n\'existe pas');
        }
        $allparcours = $dpeParcoursRepository->findByComposanteAndCampagneAndTypeValidation($composante, $this->getCampagneCollecte(), $typeValidation);


        return $this->render('validation/_liste.html.twig', [
            'process' => $process,
            'allparcours' => $allparcours,
            'etape' => $typeValidation ?? null,
        ]);
    }

    #[Route('{composante}/change-rf/liste', name: 'change_rf_liste')]
    public function changeRfListe(
        ValidationProcessChangeRf $validationProcess,
        ChangeRfRepository        $changeRfRepository,
        ComposanteRepository      $composanteRepository,
        Composante                $composante,
        Request                   $request
    ): Response
    {
        $typeValidation = $request->query->get('typeValidation');
        if (!$composante) {
            throw $this->createNotFoundException('La composante n\'existe pas');
        }

        $demandes = $changeRfRepository->findByComposanteTypeValidation(
            $composante,
            $this->getCampagneCollecte(),
            $typeValidation
        );


        return $this->render('validation/_listeChangeRf.html.twig', [
            'demandes' => $demandes,
            'etape' => $typeValidation ?? null,
        ]);
    }

    #[Route('/{composante}/fiche-matiere/liste', name: 'fiche_liste')]
    public function ficheListe(
        ValidationProcessFicheMatiere $validationProcessFicheMatiere,
        ComposanteRepository          $composanteRepository,
        FicheMatiereRepository        $ficheMatiereRepository,
        Request                       $request,
        COmposante                    $composante,
    ): Response
    {
        $typeValidation = $request->query->get('typeValidation');
        $process = $validationProcessFicheMatiere->getEtape($typeValidation);

        if (!$composante) {
            throw $this->createNotFoundException('La composante n\'existe pas');
        }
        $fiches = $ficheMatiereRepository->findByComposanteTypeValidation($composante, $this->getCampagneCollecte(), $typeValidation);

        return $this->render('validation/_listeFiches.html.twig', [
            'process' => $process,
            'fiches' => $fiches,
            'etape' => $typeValidation ?? null,
        ]);
    }
}
