<?php

namespace App\Service\Apogee\Classes;

class ParametrageAnnuelCeDTO2
{
    public string $codAnneeUni;
    public string $codSectionCnu;
    public string $codSectionCnuSec;
    public string $codRegroupementSectionCnu;
    public string $temCalculCharge;
    public string $temNormeContrainte;
    public string $temElpPorteur;
    public string $commentaireParamCE;
    public string $codEtpPorteuse;
    public string $codVrsVetPorteuse;
    public TableauElpPorteDTO $listElpPorte;
    public TableauTypeHeureDTO $listTypHeur;
    public TableauEffPrevDTO $listEffPrev;

    public function __construct(string $codAnneeUni, TableauTypeHeureDTO $typeHeureArray, string $temCalculCharge = 'O'){
        $this->codAnneeUni = $codAnneeUni;
        $this->listTypHeur = $typeHeureArray;
        $this->temCalculCharge = $temCalculCharge;
    }

    public function printInformation(): string
    {
        return $this->listTypHeur->printInformation();
    }
}
