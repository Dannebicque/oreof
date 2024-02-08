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

    /**
     * @param StructureEc|StructureUe|StructureSemestre $elementPedagogique Élément à instancier en ELP
     * @param StructureParcours $dto DTO complet du parcours en question
     * @param ?CodeNatuElpEnum $nature Nature de l'élément : Semestre (SEM), UE (UE) ou EC (MATI, MATM, ...)
     * @param bool $withChecks Si la valeur est à true, les données incorrectes lanceront des exceptions
     */
    public function __construct(
        StructureEc|StructureUe|StructureSemestre $elementPedagogique,
        StructureParcours $dto,
        ?CodeNatuElpEnum $nature = null,
        bool $withChecks = false
    ) {
        if ($elementPedagogique instanceof StructureEc){
            // Création du code ELP
            $this->codElp = $this->checkCodeApogee(
                $elementPedagogique->elementConstitutif->getCodeApogee(),
                "Type : EC - ID : {$elementPedagogique->elementConstitutif->getId()}",
                $withChecks
            ); 
            // Libellé court
            $this->libCourtElp = $this->checkLibelleEC($elementPedagogique, 25, $withChecks);
            // Libellé long
            $this->libElp = $this->checkLibelleEC($elementPedagogique, 60, $withChecks);
            // Nature
            $this->codNatureElp = $this->checkNatureElp(
                $nature, 
                "Type : EC - ID : {$elementPedagogique->elementConstitutif->getId()}",
                $withChecks
            );
            $this->codComposante = $this->checkCodeComposante(
                $dto, 
                "Type : EC - Parcours ID : {$dto->parcours->getId()}",
                $withChecks
            );
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
            $this->uniteVolume = TypeVolumeElpEnum::HE->value;
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

    private function prepareLibelle(?string $txt, int $length = 25) : string {
        if($txt){
            return mb_substr($txt, 0, $length);
        }else {
            return 'ERROR';
        }
    }

    /**
     * Vérifie que le code Apogee pour l'ELP n'est pas null.
     * @param ?string $codeApogee Code à tester
     * @param string $identifier Identifiant de l'élément si une erreur survient
     * @param bool $withChecks Vrai si une exception doit être lancée, renvoie la valeur "ERROR" sinon
     * @throws \Exception Si $withChecks est à vrai, une exception est lancée
     * @return string Code APOGEE de l'élément
     */
    private function checkCodeApogee(?string $codeApogee, string $identifier, bool $withChecks) : string {
        return $codeApogee ?? 
        (
            $withChecks === false ? "ERROR" 
            : throw new \Exception("Le code Apogee pour l'élément est incorrect. {$identifier}")
        );
    }

    /**
     * @param StructureEc $elementPedagogique Élément constitutif (matière)
     * @param int $length Longueur max de la chaîne à renvoyer
     * @param bool $withChecks Si la valeur est à vrai, une exception est lancée. Renvoie la valeur "ERROR" sinon
     * @throws \Exception Lance une exception si $withChecks est à vrai
     * @return string Libellé de l'EC raccourci à la longueur max $length
     */
    private function checkLibelleEC(
        StructureEc $elementPedagogique, 
        int $length,
        bool $withChecks = false
    ) : string {
        return $elementPedagogique->elementConstitutif->getFicheMatiere()?->getLibelle() 
            ?? $elementPedagogique->elementConstitutif->getLibelle() ?? ($withChecks === false ?
            $this->prepareLibelle(
                $elementPedagogique->elementConstitutif->getFicheMatiere()?->getLibelle() 
                ?? $elementPedagogique->elementConstitutif->getLibelle(),
                $length
        ) : throw new \Exception("Le libellé de l'EC est 'null'. ID : {$elementPedagogique->elementConstitutif->getId()}"));
    }

    /**
     * Vérification sur la nature de l'élément pédagogique
     * @param ?CodeNatuElpEnum Nature de l'élément à tester
     * @param string $errorIdentifier Identifiant pour le message d'erreur
     * @param bool $withChecks Si une Exception doit être lancée en cas d'erreur
     * @throws \Exception Lance une exception si la valeur testée n'est pas correcte et que $withChecks est true
     * @return ?CodeNatuElpEnum Nature de l'ELP
     */
    private function checkNatureElp(?CodeNatuElpEnum $nature, string $errorIdentifier, bool $withChecks) : string {
        if(($nature === null || $nature instanceof CodeNatuElpEnum === false) && $withChecks){
            throw new \Exception("La nature de l'ELP est incorrecte. {$errorIdentifier}");
        }
        return $nature->value;
    }

    /**
     * Vérifie le code composante fourni par le DTO
     * @param StructureParcours DTO du parcours
     * @param string $errorIdentifier Indication pour le message d'erreur
     * @param bool $withChecks Si une exception doit être lancée en cas d'erreur
     * @throws \Exception Si $withChecks est à vrai, lance une exception
     * @return string Code composante du parcours
     */
    private function checkCodeComposante(StructureParcours $dto, string $errorIdentifier, bool $withChecks) : string {
        return $dto->parcours->getFormation()?->getComposantePorteuse()?->getCodeComposante() 
        ?? 
        (
            $withChecks === false 
            ? "ERROR"
            : throw new \Exception("Le code de la composante porteuse est 'null'. {$errorIdentifier}")
        );
    }
}
