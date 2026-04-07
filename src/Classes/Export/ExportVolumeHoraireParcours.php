<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Export/ExportVolumeHoraireParcours.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/04/2026 18:43
 */

namespace App\Classes\Export;

use App\Classes\Excel\ExcelWriter;
use App\Entity\CampagneCollecte;
use App\Entity\Parcours;
use App\Repository\CampagneCollecteRepository;
use App\Repository\DpeParcoursRepository;
use App\Repository\VolumeHoraireParcoursRepository;
use App\Service\ProjectDirProvider;
use App\Service\VolumeHoraireParcoursCalculator;
use App\Utils\Tools;
use DateTime;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportVolumeHoraireParcours implements ExportInterface
{
    private string $fileName;
    private string $dir;

    public function __construct(
        private readonly ExcelWriter                     $excelWriter,
        ProjectDirProvider                               $projectDirProvider,
        private readonly DpeParcoursRepository           $dpeParcoursRepository,
        private readonly CampagneCollecteRepository      $campagneCollecteRepository,
        private readonly VolumeHoraireParcoursRepository $volumeHoraireParcoursRepository,
        private readonly VolumeHoraireParcoursCalculator $volumeHoraireParcoursCalculator,
    )
    {
        $this->dir = $projectDirProvider->getProjectDir() . '/public/temp/';
    }

    public function export(CampagneCollecte $campagneCollecte): StreamedResponse
    {
        $this->prepareExport($campagneCollecte);

        return $this->excelWriter->genereFichier($this->fileName);
    }

    private function prepareExport(CampagneCollecte $campagneCollecte): void
    {
        $dpeParcoursList = $this->dpeParcoursRepository->findByCampagneCollecte($campagneCollecte);
        $campagnePrecedente = $this->campagneCollecteRepository->findOneBy(['annee' => $campagneCollecte->getAnnee() - 1]);

        $this->excelWriter->nouveauFichier('Volumes horaires');
        $this->excelWriter->setActiveSheetIndex(0);

        $libelleCampagnePrecedente = $campagnePrecedente instanceof CampagneCollecte
            ? $this->getLibelleCampagne($campagnePrecedente)
            : 'Campagne précédente';
        $libelleCampagneCourante = $this->getLibelleCampagne($campagneCollecte);

        $this->excelWriter->writeCellXY(1, 1, 'Type diplôme');
        $this->excelWriter->writeCellXY(2, 1, 'Diplôme');
        $this->excelWriter->writeCellXY(3, 1, 'Composante');
        $this->excelWriter->writeCellXY(4, 1, 'Parcours');
        $this->excelWriter->writeCellXY(5, 1, 'Heures ' . $libelleCampagnePrecedente);
        $this->excelWriter->writeCellXY(6, 1, 'Heures ' . $libelleCampagneCourante);
        $this->excelWriter->writeCellXY(7, 1, 'Ecart maquette');
        $this->excelWriter->writeCellXY(8, 1, 'Commentaire');

        $ligne = 2;
        foreach ($dpeParcoursList as $dpeParcours) {
            $parcours = $dpeParcours->getParcours();
            if (!$parcours instanceof Parcours) {
                continue;
            }

            $formation = $parcours->getFormation();
            if ($formation === null) {
                continue;
            }

            $previousVolume = null;
            if ($campagnePrecedente instanceof CampagneCollecte) {
                $previousReference = $parcours->getParcoursOrigineCopie() ?? $parcours;
                $previousVolume = $this->volumeHoraireParcoursRepository->findOneByParcoursAndCampagne(
                    $previousReference,
                    $campagnePrecedente,
                );
            }

            $commentaire = $parcours->getCommentaire() ?? '';
            $heuresCourantes = '';
            $heuresPrecedentes = $previousVolume?->getHeuresTotal();
            $ecart = '';

            try {
                $currentVolume = $this->volumeHoraireParcoursCalculator->calculate($parcours, $campagneCollecte);
                $heuresCourantes = $this->formatHeures($currentVolume->getHeuresTotal());

                if ($heuresPrecedentes !== null) {
                    $ecart = $this->formatHeures($currentVolume->getHeuresTotal() - $heuresPrecedentes);
                }
            } catch (\Throwable $e) {
                $commentaire = trim($commentaire . ' ' . 'Erreur calcul volume : ' . $e->getMessage());
            }

            $this->excelWriter->writeCellXY(1, $ligne, $formation->getTypeDiplome()?->getLibelle() ?? '');
            $this->excelWriter->writeCellXY(2, $ligne, $formation->getDisplay());
            $this->excelWriter->writeCellXY(3, $ligne, $formation->getComposantePorteuse()?->getLibelle() ?? '');
            $this->excelWriter->writeCellXY(4, $ligne, $parcours->isParcoursDefaut() ? 'Pas de parcours' : $parcours->getDisplay());
            $this->excelWriter->writeCellXY(5, $ligne, $heuresPrecedentes !== null ? $this->formatHeures($heuresPrecedentes) : '');
            $this->excelWriter->writeCellXY(6, $ligne, $heuresCourantes);
            $this->excelWriter->writeCellXY(7, $ligne, $ecart);
            $this->excelWriter->writeCellXY(8, $ligne, $commentaire);
            $ligne++;
        }

        $this->excelWriter->getColumnsAutoSize('A', 'H');
        $this->fileName = Tools::FileName('Volumes-horaires-parcours-' . $libelleCampagneCourante . '-' . (new DateTime())->format('d-m-Y-H-i'), 60);
    }

    private function getLibelleCampagne(CampagneCollecte $campagneCollecte): string
    {
        return $campagneCollecte->getAnneeUniversitaire()?->getLibelle()
            ?? sprintf('%d/%d', $campagneCollecte->getAnnee() - 1, $campagneCollecte->getAnnee());
    }

    private function formatHeures(float $heures): float|int
    {
        $heures = round($heures, 2);

        if ((float)(int)$heures === $heures) {
            return (int)$heures;
        }

        return $heures;
    }

    public function exportLink(CampagneCollecte $campagneCollecte): string
    {
        $this->prepareExport($campagneCollecte);
        $this->excelWriter->saveFichier($this->fileName, $this->dir . 'zip/');

        return $this->fileName . '.xlsx';
    }
}

