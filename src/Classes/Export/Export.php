<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Export/Export.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/07/2023 10:28
 */

namespace App\Classes\Export;

use App\Classes\MyPDF;
use App\Entity\AnneeUniversitaire;
use App\Repository\FormationRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use DateTimeInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class Export
{
    private string $format;
    private string $typeDocument;
    private array $formations;
    private AnneeUniversitaire $annee;
    private DateTimeInterface $date;
    private string $dir;
    private mixed $export;

    public function __construct(
        protected ExportMccc $exportMccc,
        KernelInterface $kernel,
        private TypeDiplomeRegistry $typeDiplomeRegistry,
        private MyPDF $myPDF
    )
    {
        $this->dir = $kernel->getProjectDir().'/public/temp';
    }

    public  function setDate(DateTimeInterface $date):void
    {
        $this->date = $date;
    }

    public function setTypeDocument(string $typeDocument)
    {
        $t = explode('-', $typeDocument);
        $this->format = $t[0];
        $this->typeDocument = $t[1];
    }

    public function exportFormations(array $formations, AnneeUniversitaire $annee): string
    {
        $this->formations = $formations;
        $this->annee = $annee;
        return $this->export();
    }

    private function export(): string
    {
        switch ($this->typeDocument) {
            case 'fiche':
                return $this->exportConseil();
            case 'mccc':
                return $this->exportMccc();
            case 'light_mccc':
                return $this->exportMccc(true);
        }
    }

    private function exportConseil() : string
    {
        $this->export = new ExportConseil(
            $this->dir,
            $this->myPDF,
            $this->formations,
            $this->annee,
            $this->date);
        return $this->export->exportZip();
    }

    private function exportMccc(bool $isLight = false) : string
    {
        $this->exportMccc->export(
            $this->dir,
            $this->typeDiplomeRegistry,
            $this->formations,
            $this->annee,
            $this->date,
            $this->format,
            $isLight);
        return $this->exportMccc->exportZip();
    }
}
