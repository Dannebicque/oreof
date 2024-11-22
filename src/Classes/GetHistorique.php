<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/GetHistorique.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 06/10/2023 07:58
 */

namespace App\Classes;

use App\Entity\ChangeRf;
use App\Entity\DpeParcours;
use App\Entity\Formation;
use App\Entity\HistoriqueFormation;
use App\Entity\HistoriqueParcours;
use App\Entity\Parcours;
use App\Repository\HistoriqueFormationRepository;
use App\Repository\HistoriqueParcoursRepository;

class GetHistorique
{
    public function __construct(
        protected HistoriqueParcoursRepository $historiqueParcoursRepository,
        protected HistoriqueFormationRepository $historiqueFormationRepository
    )
    {}

    public  function getHistoriqueParcours(Parcours $parcours): array
    {
        return $this->historiqueParcoursRepository->findByParcours($parcours);
    }

    public  function getHistoriqueFormation(Formation $formation): array
    {
        return $this->historiqueFormationRepository->findByFormation($formation);
    }

    public function getHistoriqueFormationLastStep(Formation $formation, string $step): ?HistoriqueFormation
    {
        return $this->historiqueFormationRepository->findByFormationLastStep($formation, $step);
    }

    public function getHistoriqueFormationHasPv(Formation $formation): bool
    {
        $conseil = $this->historiqueFormationRepository->findByFormationLastStep($formation, 'conseil');
        return $conseil !== null && array_key_exists('fichier', $conseil->getComplements());
    }

    public function getHistoriqueParcoursLastStep(DpeParcours $dpeParcours, string $step): ?HistoriqueParcours
    {
        return $this->historiqueParcoursRepository->findByParcoursLastStep($dpeParcours->getParcours(), $step);

    }

    public function getHistoriqueChangeRfLastStep(?ChangeRf $changeRf, string $step): ?HistoriqueFormation
    {
        return $this->historiqueFormationRepository->findByChangeRfLastStep($changeRf, $step);
    }
}
