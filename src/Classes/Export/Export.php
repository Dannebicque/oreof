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
use App\Entity\CampagneCollecte;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\Tools;
use DateTimeInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class Export
{
    private string $format;
    private string $typeDocument;
    private array $formations;
    private ?CampagneCollecte $annee;
    private ?DateTimeInterface $date;
    private string $dir;
    private mixed $export;

    public function __construct(
        protected ExportFicheMatiere $exportFicheMatiere,
        protected ExportRegime       $exportRegime,
        protected ExportCfvu         $exportCfvu,
        protected ExportCarif        $exportCarif,
        protected ExportSynthese     $exportSynthese,
        protected ExportSeip           $exportSeip,
        protected ExportEc           $exportEc,
        protected ExportMccc         $exportMccc,
        KernelInterface              $kernel,
        private TypeDiplomeRegistry  $typeDiplomeRegistry,
        private MyPDF                $myPDF
    ) {
        $this->dir = $kernel->getProjectDir().'/public/temp';
    }

    public function setDate(?DateTimeInterface $date):void
    {
        $this->date = $date;
    }

    public function setTypeDocument(string $typeDocument)
    {
        $t = explode('-', $typeDocument);
        $this->format = $t[0];
        $this->typeDocument = $t[1];
    }

    public function exportFormations(array $formations, ?CampagneCollecte $annee = null): string
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
            case 'carif':
                return $this->exportCarif();
            case 'regime':
                return $this->exportRegime();
            case 'cfvu':
                return $this->exportCfvu();
            case 'fiches_matieres':
                return $this->exportFicheMatiere();
            case 'seip':
                return $this->exportSeip();
            case 'ec':
                return $this->exportEc();
            case 'synthese':
                return $this->exportSynthese();
        }
    }

    private function exportConseil() : string
    {
        $this->export = new ExportConseil(
            $this->dir,
            $this->myPDF,
            $this->formations,
            $this->annee,
            $this->date
        );
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
            $isLight
        );
        return $this->exportMccc->exportZip();
    }

    private function exportCarif()
    {
        return $this->exportCarif->exportLink($this->annee);
    }

    private function exportSeip()
    {
        return $this->exportSeip->exportLink($this->annee);
    }

    private function exportEc()
    {
        return $this->exportEc->exportLink($this->annee);
    }

    private function exportSynthese(): string
    {
        return $this->exportSynthese->exportLink($this->annee);
    }

    private function exportRegime()
    {
        return $this->exportRegime->exportLink($this->annee);
    }

    private function exportCfvu()
    {
        return $this->exportCfvu->exportLink($this->annee);
    }

    private function exportFicheMatiere()
    {
        return $this->exportFicheMatiere->exportLink($this->formations);
    }
}
