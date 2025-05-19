<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Export/ExportSynthese.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/11/2023 13:07
 */

namespace App\Classes\Export;

use App\Classes\MyGotenbergPdf;
use App\Entity\CampagneCollecte;
use App\Repository\FormationRepository;
use App\Repository\TypeEpreuveRepository;
use App\Service\VersioningParcours;
use App\Service\VersioningStructureExtractDiff;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\Tools;
use Symfony\Component\HttpKernel\KernelInterface;

class ExportSyntheseModification
{
    private string $dir;

    public function __construct(
        protected TypeEpreuveRepository $typeEpreuveRepository,
        protected TypeDiplomeRegistry $typeDiplomeRegistry,
        protected VersioningParcours $versioningParcours,
        protected MyGotenbergPdf      $myGotenbergPdf,
        KernelInterface               $kernel,
        protected FormationRepository $formationRepository,
    ) {
        $this->dir = $kernel->getProjectDir() . '/public/';
    }

    public function exportLink(array $formations, CampagneCollecte $campagneCollecte): string
    {
        $epreuves = $this->typeEpreuveRepository->findAll();
        foreach ($epreuves as $epreuve) {
            $typeEpreuves[$epreuve->getId()] = $epreuve;
        }

        foreach ($formations as $formation) {
            $tDemandes = [];

            $form = $formation['formation'];
            foreach ($formation['parcours'] as $parc) {
                $typeD = $this->typeDiplomeRegistry->getTypeDiplome($form?->getTypeDiplome()?->getModeleMcc());
                $dto = $typeD->calculStructureParcours($parc, true, false);
                $structureDifferencesParcours = $this->versioningParcours->getStructureDifferencesBetweenParcoursAndLastVersion($parc);
                if ($structureDifferencesParcours !== null) {
                    $diffStructure = new VersioningStructureExtractDiff($structureDifferencesParcours, $dto, $typeEpreuves);
                    $diffStructure->extractDiff();
                } else {
                    $diffStructure = null;
                }
                $tDemandes[] = [
                    'formation' => $form,
                    'composante' => $formation['composante'],
                    'dpeDemande' => $formation['dpeDemande'],
                    'parcours' => $parc,
                    'diffStructure' => $diffStructure,
                    'dto' => $dto
                ];
            }
//            dump($form->getSlug());
//            dump($tDemandes);

            $fichiers[] = $this->myGotenbergPdf->renderAndSave(
                'pdf/synthese_modifications.html.twig',
                'pdftests/',
                [
                    'titre' => 'Liste des demande de changement MCCC et maquettes',
                    'demandes' => $tDemandes,
                    'dpe' => $campagneCollecte,
                ],
                Tools::FileName($form->getSlug())
            );
        }

        $zip = new \ZipArchive();
        $fileName = 'synthese_modification_cfvu_' . date('YmdHis') . '.zip';
        $zipName = $this->dir . 'temp/zip/' . $fileName;
        $zip->open($zipName, \ZipArchive::CREATE);

        foreach ($fichiers as $fichier) {
            $zip->addFile(
                $this->dir . 'pdftests/' . $fichier,
                $fichier
            );
        }

        $zip->close();

        foreach ($fichiers as $fichier) {
            if (file_exists($this->dir . 'pdftests/' . $fichier)) {
                unlink($this->dir . 'pdftests/' . $fichier);
            }
        }

        return $fileName;
    }
}
