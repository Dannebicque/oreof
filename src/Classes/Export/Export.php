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
use DateTimeInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class Export
{
    private string $format;
    private string $typeDocument;
    private array $formations;
    private ?CampagneCollecte $campagneCollecte;
    private ?DateTimeInterface $date;
    private string $dir;
    private mixed $export;

    public function __construct(
        protected ExportFicheMatiere $exportFicheMatiere,
        protected ExportRegime      $exportRegime,
        protected ExportCfvu        $exportCfvu,
        protected ExportCarif       $exportCarif,
        protected ExportCap         $exportCap,
        protected ExportFiabilisation         $exportFiabilisation,
        protected ExportSynthese    $exportSynthese,
        protected ExportSeip        $exportSeip,
        protected ExportEc          $exportEc,
        protected ExportListeFicheMatiere          $exportListeFicheMatiere,
        protected ExportMccc        $exportMccc,
        KernelInterface             $kernel,
        private TypeDiplomeRegistry $typeDiplomeRegistry,
        private MyPDF               $myPDF,
        private readonly ExportSyntheseModification $exportSyntheseModification
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

    public function exportFormations(array $formations, ?CampagneCollecte $campagneCollecte = null): string
    {
        $this->formations = $formations;
        $this->campagneCollecte = $campagneCollecte;
        return $this->export();
    }

    private function export(): string
    {
        switch ($this->typeDocument) {
            case 'fiche':
                return $this->exportConseil();
            case 'listefiche':
                return $this->exportListeFicheMatiere();
            case 'mccc':
                return $this->exportMccc();
            case 'light_mccc':
                return $this->exportMccc(true);
            case 'version_mccc':
                return $this->exportMcccVersion();
            case 'carif':
                return $this->exportCarif();
            case 'regime':
                return $this->exportRegime();
            case 'responsable':
                return $this->exportRegime();
            case 'cfvu':
                return $this->exportCfvu();
            case 'cap':
                return $this->exportCap();
            case 'fiabilisation':
                return $this->exportFiabilisation();
            case 'fiches_matieres':
                return $this->exportFicheMatiere();
            case 'seip':
                return $this->exportSeip();
            case 'ec':
                return $this->exportEc();
            case 'synthese':
                return $this->exportSynthese();
            case 'synthese_modification':
                return $this->exportSyntheseModifications();
        }
    }

    private function exportConseil() : string
    {
        $this->export = new ExportConseil(
            $this->dir,
            $this->myPDF,
            $this->formations,
            $this->campagneCollecte,
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
            $this->campagneCollecte,
            $this->date,
            $this->format,
            $isLight
        );
        return $this->exportMccc->exportZip();
    }

    private function exportCarif() : string
    {
        return $this->exportCarif->exportLink($this->campagneCollecte);
    }

    private function exportSeip() : string
    {
        return $this->exportSeip->exportLink($this->campagneCollecte);
    }

    private function exportEc() : string
    {
        return $this->exportEc->exportLink($this->campagneCollecte);
    }

    private function exportListeFicheMatiere() : string
    {
        return $this->exportListeFicheMatiere->exportLink($this->campagneCollecte);
    }

    private function exportSynthese(): string
    {
        return $this->exportSynthese->exportLink($this->campagneCollecte);
    }

    private function exportRegime() : string
    {
        return $this->exportRegime->exportLink($this->campagneCollecte);
    }

    private function exportCfvu() : string
    {
        return $this->exportCfvu->exportLink($this->campagneCollecte);
    }

    private function exportFicheMatiere() : string
    {
        return $this->exportFicheMatiere->exportLink($this->formations);
    }

    private function exportCap() : string
    {
        return $this->exportCap->exportLink($this->formations);
    }

    private function exportFiabilisation() : string
    {
        return $this->exportFiabilisation->exportLink($this->formations);
    }

    private function exportSyntheseModifications(): string
    {
        return $this->exportSyntheseModification->exportLink($this->formations, $this->campagneCollecte);
    }

    private function exportMcccVersion()
    {
        $this->exportMccc->exportVersion(
            $this->dir,
            $this->typeDiplomeRegistry,
            $this->formations,
            $this->campagneCollecte,
        );
        return $this->exportMccc->exportVersionZip();
    }
}
