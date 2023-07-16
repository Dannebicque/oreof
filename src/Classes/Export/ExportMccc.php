<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Export/ExportMccc.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/07/2023 10:49
 */

namespace App\Classes\Export;

use App\Entity\AnneeUniversitaire;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\Tools;
use DateTime;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

class ExportMccc
{
    public function __construct(
        private TypeDiplomeRegistry $typeDiplomeRegistry,
        private array $formations,
        private AnneeUniversitaire $annee,
        private DateTimeInterface $date
    )
    {
    }

    public function exportZip(): string
    {
        $zip = new \ZipArchive();
        $fileName = 'export_mccc_' . date('YmdHis') . '.zip';
        $zipName = 'temp/zip/'.$fileName;
        $zip->open($zipName, \ZipArchive::CREATE);


        $tabFiles = [];
        $dir = 'temp/mccc/';

        foreach ($this->formations as $formation) {
            $typeDiplome = $this->typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome()?->getModeleMcc());
            foreach ($formation->getParcours() as $parcours) {
                $fichier = $typeDiplome->exportAndSaveExcelMccc(
                    $dir,
                    $this->annee,
                    $parcours,
                    $this->date
                );

                $tabFiles[] = $fichier;
                $zip->addFile(
                    $dir.$fichier,
                    $formation->getDisplay().'/'.$fichier
                );
            }
        }

        $zip->close();

        // suppression des PDF
        foreach ($tabFiles as $file) {
            if (file_exists($dir.$file)) {
                unlink($dir.$file);
            }
        }

        return $fileName;
    }
}
