<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Export/ExportConseil.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/07/2023 10:48
 */

namespace App\Classes\Export;

use App\Entity\CampagneCollecte;
use DateTimeInterface;
use ZipArchive;

class ExportConseil
{
    public function __construct(
        private string            $dir,
        private array             $formations,
        private CampagneCollecte  $annee,
        private DateTimeInterface $date
    ) {
    }

    public function exportZip(): string
    {
        $zip = new ZipArchive();
        $fileName = 'export_conseil_' . date('YmdHis') . '.zip';
        $zipName = $this->dir. '/zip/' . $fileName;
        $zip->open($zipName, ZipArchive::CREATE);

        $tabFiles = [];
        $dir = $this->dir.'/pdf/';

        foreach ($this->formations as $formation) {
            $tParcours = [];
            $calculStructureParcours = new CalculStructureParcours();
            foreach ($formation->getParcours() as $parcours) {
                $tParcours[$parcours->getId()] = $calculStructureParcours->calcul($parcours);
            }
            $fichier = $this->myPDF::genereAndSavePdf('pdf/conseil.html.twig', [
                'formation' => $formation,
                'typeDiplome' => $formation->getTypeDiplome(),
                'tParcours' => $tParcours,
            ], 'dpe_formation_' . $formation->getDisplay(), $dir);

            $tabFiles[] = $fichier;
            $zip->addFile(
                $dir . $fichier,
                $formation->getDisplay() . '/' . $fichier
            );
        }

        $zip->close();

        // suppression des PDF
        foreach ($tabFiles as $file) {
            if (file_exists($dir . $file)) {
                unlink($dir . $file);
            }
        }

        return $fileName;
    }
}
