<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Export/ExportFicheMatiere.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/11/2023 14:53
 */

namespace App\Classes\Export;

use App\Classes\MyGotenbergPdf;
use App\Repository\ParcoursRepository;
use App\Utils\Tools;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use ZipArchive;

class ExportFicheMatiere
{
    private string $dir;
    public function __construct(
        KernelInterface $kernel,
        protected MyGotenbergPdf     $myPdf,
        protected ParcoursRepository $parcoursRepository
    )
    {
        $this->dir = $kernel->getProjectDir().'/public/';
    }

    public function exportLink(array $idParcours)
    {
        $parcours = $this->parcoursRepository->find($idParcours[0]);
        if ($parcours === null) {
            throw new Exception('Parcours non trouvé');
        }
        $ecs = $parcours->getElementConstitutifs();
        $formation = $parcours->getFormation();
        if ($formation === null) {
            throw new Exception('Formation non trouvée');
        }
        $typeDiplome = $formation?->getTypeDiplome();
        $fichiers = [];
        foreach ($ecs as $ec) {
            $ficheMatieres = $ec->getFicheMatiere();
            if ($ficheMatieres !== null) {
                $fichiers[] = $this->myPdf->renderAndSave(
                    'pdf/ficheMatiere.html.twig',
                    'pdftests/',
                    [
                        'ec' => $ec,
                        'formation' => $formation,
                        'semestre' => $ec->getUe()?->getSemestre(),
                        'parcours' => $parcours,
                        'typeDiplome' => $typeDiplome,
                        'titre' => 'Fiche EC/matière ' . $ficheMatieres->getLibelle(),
                    ],
                    Tools::FileName($ficheMatieres->getSlug())
                );
            }
        }

        $zip = new ZipArchive();
        $fileName = 'export_fiches_matieres_' . date('YmdHis') . '.zip';
        $zipName = $this->dir . 'temp/zip/' . $fileName;
        $zip->open($zipName, ZipArchive::CREATE);

        foreach ($fichiers as $fichier) {
            $zip->addFile(
                $this->dir . 'pdftests/' . $fichier,
                $fichier
            );
        }

        $zip->close();

        foreach ($fichiers as $fichier) {
            if (file_exists($this->dir. 'pdftests/' . $fichier)) {
                unlink($this->dir . 'pdftests/'. $fichier);
            }
        }

        return $fileName;
    }
}
