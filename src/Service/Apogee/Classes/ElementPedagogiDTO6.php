<?php

namespace App\Service\Apogee\Classes;

use App\DTO\StructureEc;
use App\DTO\StructureParcours;
use App\DTO\StructureSemestre;
use App\DTO\StructureUe;
use App\Enums\Apogee\CodeNatuElpEnum;
use App\Enums\Apogee\TypeVolumeElpEnum;

class ElementPedagogiDTO6
{
    public string $codElp;
    public string $libCourtElp;
    public string $libElp;
    public string $codNatureElp;
    public string $codComposante;
    public string $temSuspension;
    public string $motifSuspension;
    public string $temModaliteControle;
    public string $temEnsADistance;
    public string $temHorsEtab;
    public string $temStage;
    public string $lieu;
    public string $nbrCredits;
    public string $dateDebutIP;
    public string $dateFinIP;
    public string $descriptionElp;
    public string $volume;
    public string $uniteVolume;
    public string $codPeriode;
    public string $numOrdrePresentationElp;
    public string $seuilOuverture;
    public string $capaciteMaxElp;
    public string $capaciteIntElp;
    public TableauComposanteDTO2 $listComposantesAssociees;
    public TableauCentreInsPedagogiDTO $listCentreInsPedagogi;
    public TableauPersonnelResponsableDTO $listResponsables;
    public TableauLibellesAnnexeDTO $listLibAnnexe;
    public TableauParametrageChargeEnseignementDTO2 $listParamChargEns;
    public TableauElementPrerequisDTO2 $listElementPrerequis;
    public TableauTypePopulationDTO2 $listTypePopulation;

    public function __construct(
        StructureEc|StructureUe|StructureSemestre $elementPedagogique,
        StructureParcours $dto,
        ?CodeNatuElpEnum $nature = null
    ) {
        if ($elementPedagogique instanceof StructureEc){
            $this->codElp = $elementPedagogique->elementConstitutif->getCodeApogee() ?? "ERROR";
            $this->libCourtElp = $this->prepareLibelle(
                $elementPedagogique->elementConstitutif->getFicheMatiere()?->getLibelle() 
                ?? $elementPedagogique->elementConstitutif->getLibelle(),
                25
            );
            $this->libElp = $this->prepareLibelle(
                $elementPedagogique->elementConstitutif->getFicheMatiere()?->getLibelle()
                ?? $elementPedagogique->elementConstitutif->getLibelle(), 
                60
            );
            $this->codNatureElp = $nature->value;
            $this->codComposante = $dto->parcours->getFormation()?->getComposantePorteuse()?->getCodeComposante() ?? "ERROR";
            // $this->temSuspension;
            // $this->motifSuspension;
            $this->temModaliteControle = "O";
            // $this->temEnsADistance;
            // $this->temHorsEtab;
            // $this->temStage;
            // $this->lieu;
            $this->nbrCredits = $elementPedagogique->heuresEctsEc->ects;
            // $this->dateDebutIP;
            // $this->dateFinIP;
            // $this->descriptionElp;
            $this->volume = $elementPedagogique->heuresEctsEc->sommeEcTotalPresDist();
            $this->uniteVolume = TypeVolumeElpEnum::HE->value;
            $this->codPeriode = $elementPedagogique->elementConstitutif->getUe()->getSemestre()->display();
            // $this->numOrdrePresentationElp;
            // $this->seuilOuverture;
            // $this->capaciteMaxElp;
            // $this->capaciteIntElp;
            // $this->listComposantesAssociees;
            $this->listCentreInsPedagogi = new TableauCentreInsPedagogiDTO(
                array_map(
                    fn($composante) => $composante->getCodeApogee(),
                    $dto->parcours->getFormation()?->getComposantesInscription()?->toArray() ?? []
                )
            );
            // $this->listResponsables;
            // $this->listLibAnnexe;
            // $this->listParamChargEns;
            // $this->listElementPrerequis;
            // $this->listTypePopulation;
        }
        elseif ($elementPedagogique instanceof StructureUe){
            $this->codElp = $elementPedagogique->ue->getCodeApogee() ?? "ERROR";
            $this->libCourtElp = $this->prepareLibelle($elementPedagogique->ue->getLibelle(), 25);
            $this->libElp = $this->prepareLibelle($elementPedagogique->ue->getLibelle(), 60);
            $this->codNatureElp = $nature->value;
            $this->codComposante = $dto->parcours->getFormation()?->getComposantePorteuse()?->getCodeComposante() ?? "ERROR";
            // $this->temSuspension
            // $this->motifSuspension
            $this->temModaliteControle = "O"; // valeur par défaut
            // $this->temEnsADistance = "N"; // valeur par défaut
            // $this->temHorsEtab = "N"; // valeur par défaut
            // $this->temStage = "N"; // valeur par défaut
            // $this->lieu = ""; //
            $this->nbrCredits = $elementPedagogique->heuresEctsUe->sommeUeEcts;
            // $this->dateDebutIP
            // $this->dateFinIP
            // $this->descriptionElp
            $this->volume = $elementPedagogique->heuresEctsUe->sommeUeTotalPresDist();
            $this->uniteVolume = TypeVolumeElpEnum::HE->value; // Avoir si l'on met en semestre ou en heure
            $this->codPeriode = $elementPedagogique->ue->getSemestre()->display();
            // $this->numOrdrePresentationElp
            // $this->seuilOuverture
            // $this->capaciteMaxElp
            // $this->capaciteIntElp
            // $this->listComposantesAssociees = new TableauComposanteDTO2([]);
            $this->listCentreInsPedagogi = new TableauCentreInsPedagogiDTO(
                array_map(
                    fn($composante) => $composante->getCodeApogee(),
                    $dto->parcours->getFormation()?->getComposantesInscription()?->toArray() ?? []
                )
            );
            // $this->listResponsables;
            // $this->listLibAnnexe;
            // $this->listParamChargEns;
            // $this->listElementPrerequis;
            // $this->listTypePopulation;
        
        }
        elseif ($elementPedagogique instanceof StructureSemestre){
            $this->codElp = $elementPedagogique->semestre->getCodeApogee() ?? "ERROR";
            // Libellés
            if( $elementPedagogique->semestre->getOrdre() 
                && $dto->parcours->getSigle() 
                && $dto->parcours->getFormation()?->getSigle()
                && $dto->parcours->getFormation()?->getTypeDiplome()->getLibelleCourt()
            ){
                $libelleLong = 'Semestre ' . $elementPedagogique->semestre->getOrdre() 
                    . ' ' 
                    . $dto->parcours->getFormation()->getTypeDiplome()->getLibelleCourt()
                    . ' '
                    . $dto->parcours->getFormation()->getSigle() 
                    . ' ' 
                    . $dto->parcours->getSigle();
                $libelleCourt = 'SEM ' . $elementPedagogique->semestre->getOrdre()
                    . ' '
                    . $dto->parcours->getFormation()->getTypeDiplome()->getLibelleCourt()
                    . ' '
                    . $dto->parcours->getSigle();
            }
            else {
                $libelleLong = "ERROR";
                $libelleCourt = "ERROR";
            }
            $this->libCourtElp = $this->prepareLibelle($libelleCourt, 25);
            $this->libElp = $this->prepareLibelle($libelleLong, 60);
            $this->codNatureElp = CodeNatuElpEnum::SEM->value;
            $this->codComposante = $dto->parcours->getFormation()?->getComposantePorteuse()?->getCodeComposante() ?? "ERROR";
            // $this->temSuspension;
            // $this->motifSuspension;
            $this->temModaliteControle = "O";
            // $this->temEnsADistance;
            // $this->temHorsEtab;
            // $this->temStage;
            // $this->lieu;
            $this->nbrCredits = $elementPedagogique->heuresEctsSemestre->sommeSemestreEcts;
            // $this->dateDebutIP;
            // $this->dateFinIP;
            // $this->descriptionElp;
            $this->volume = $elementPedagogique->heuresEctsSemestre->sommeSemestreTotalPresDist();
            $this->uniteVolume = TypeVolumeElpEnum::ST->value;
            $this->codPeriode = $elementPedagogique->semestre->display();
            // $this->numOrdrePresentationElp;
            // $this->seuilOuverture;
            // $this->capaciteMaxElp;
            // $this->capaciteIntElp;
            // $this->listComposantesAssociees;
            $this->listCentreInsPedagogi = new TableauCentreInsPedagogiDTO(
                array_map(
                    fn($composante) => $composante->getCodeApogee(),
                    $dto->parcours->getFormation()?->getComposantesInscription()?->toArray() ?? []
                )
            );
            // $this->listResponsables;
            // $this->listLibAnnexe;
            // $this->listParamChargEns;
            // $this->listElementPrerequis;
            // $this->listTypePopulation;
        }
    }

    private function prepareLibelle(?string $txt, int $length = 25){
        if($txt){
            return substr($txt, 0, $length);
        }else {
            return 'ERROR';
        }
    }
}
