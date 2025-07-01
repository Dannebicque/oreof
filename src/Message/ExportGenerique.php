<?php

namespace App\Message;

use App\Entity\CampagneCollecte;

class ExportGenerique {

    /** 
     * @var array $typeExport Contient le type d'export (PDF / Xlsx) 
     * et si ce sont les fiches ou les parcours 
     * 
     */
    private array $typeExport;

    private array $parcoursIdArray;

    private array $fieldValueArray;

    private int $campagneCollecte;

    private string $withFieldSorting;

    private string $emailDestinataire;

    private string $withDefaultHeader;

    public function __construct(
        array $typeExport,
        array $parcoursIdArray,
        array $fieldValueArray,
        int $campagneCollecte,
        string $withFieldSorting,
        string $emailDestinataire,
        string $withDefaultHeader
    ) {
        $this->typeExport = $typeExport;
        $this->parcoursIdArray = $parcoursIdArray;
        $this->fieldValueArray = $fieldValueArray;
        $this->campagneCollecte = $campagneCollecte;
        $this->withFieldSorting = $withFieldSorting;
        $this->emailDestinataire = $emailDestinataire;
        $this->withDefaultHeader = $withDefaultHeader;
    }

    public function getTypeExport() {
        return $this->typeExport;
    }

    public function getParcoursIdArray() {
        return $this->parcoursIdArray;
    }

    public function getFieldValueArray() {
        return $this->fieldValueArray;
    }

    public function getCampagneCollecte() {
        return $this->campagneCollecte;
    }

    public function getWithFieldSorting() {
        return $this->withFieldSorting;
    }

    public function getEmailDestinataire() {
        return $this->emailDestinataire;
    }

    public function hasDefaultHeader(){
        return $this->withDefaultHeader;
    }
}