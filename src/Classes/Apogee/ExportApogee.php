<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Apogee/ExportApogee.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/01/2024 19:30
 */

namespace App\Classes\Apogee;

use App\Classes\CalculStructureParcours;
use App\DTO\Apogee\Elp;
use App\DTO\StructureSemestre;
use App\DTO\StructureUe;
use App\Entity\Parcours;
use App\Enums\Apogee\CodeNatuElpEnum;
use App\Enums\Apogee\TypeVolumeElpEnum;
use App\Repository\ElementConstitutifRepository;
use Doctrine\ORM\EntityManagerInterface;

class ExportApogee
{
    public array $tabsElp = [];
    public Parcours $parcours;

    public function __construct(
        protected EntityManagerInterface       $entityManager,
        protected ElementConstitutifRepository $elementConstitutifRepository
    ) {
    }

    public function genereExportApogee(Parcours $parcours)
    {
        $this->parcours = $parcours;
        $calculStructureParcours = new CalculStructureParcours($this->entityManager, $this->elementConstitutifRepository);
        $structureParcours = $calculStructureParcours->calcul($parcours);

        foreach ($structureParcours->semestres as $semestre) {
            if ($semestre->semestre->isNonDispense() === false) {
                $this->tabsElp[] = $this->genereElpSemestre($semestre);
                foreach ($semestre->ues() as $ue) {
                    $this->tabsElp[] = $this->genereElpUe($ue, $semestre);
//                foreach ($ue->getElps() as $elp) {
//                    //on genere l'elp
//                    $this->genereElp($elp);
//                }
                }
            }
        }
    }

    private function genereElpSemestre(StructureSemestre $semestre): Elp
    {
        $elp = new Elp();
        $elp->codElp = $semestre->semestre->getCodeApogee() ?? '-erreur-';
        $elp->libElp = $this->prepareLibelle('Semestre ' . $semestre->semestre->getOrdre(), 60);
        $elp->libCourtElp = $this->prepareLibelle($semestre->semestre->display(), 25);
        $elp->codNatureElp = CodeNatuElpEnum::SEM;
        $elp->codComposante = $this->parcours->getFormation()?->getComposantePorteuse()?->getCodeComposante() ?? 'err';
        $elp->nbrCredits = $semestre->heuresEctsSemestre->sommeSemestreEcts;
        $elp->volume = $semestre->heuresEctsSemestre->sommeSemestreTotalPresDist();
        $elp->uniteVolume = TypeVolumeElpEnum::ST;
        $elp->codPeriode = $semestre->semestre->display();

        return $elp;
    }

    private function genereElpUe(StructureUe $ue, StructureSemestre $semestre): Elp
    {
        $elp = new Elp();
        $elp->codElp = $ue->ue->getCodeApogee() ?? 'err';
        $elp->libCourtElp = $this->prepareLibelle($ue->ue->getLibelle(), 25);
        $elp->libElp = $this->prepareLibelle($ue->ue->getLibelle(), 60);
        $elp->codNatureElp = CodeNatuElpEnum::UE;
        $elp->codComposante = $this->parcours->getFormation()?->getComposantePorteuse()?->getCodeComposante() ?? 'err';
        $elp->nbrCredits = $ue->heuresEctsUe->sommeUeEcts;
        $elp->volume = $ue->heuresEctsUe->sommeUeTotalPresDist();
        $elp->uniteVolume = TypeVolumeElpEnum::ST;
        $elp->codPeriode = $semestre->semestre->display();


        return $elp;
    }

    private function prepareLibelle(?string $getLibelle, int $int = 25): string
    {
        if ($getLibelle !== null) {
            return substr($getLibelle, 0, $int);
        }

        return 'err';
    }
}
