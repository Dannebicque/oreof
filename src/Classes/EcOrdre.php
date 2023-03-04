<?php

namespace App\Classes;

use App\Entity\Ue;
use App\Repository\EcUeRepository;
use App\Repository\ElementConstitutifRepository;
use Doctrine\ORM\EntityManagerInterface;

class EcOrdre
{

    public function __construct(private EntityManagerInterface $entityManager,
        private EcUeRepository $ecUeRepository,
        private ElementConstitutifRepository $elementConstitutifRepository)
    {
    }


    public function getOrdreSuivant(Ue $ue): int
    {
        $ordreMax = $this->ecUeRepository->findLastEc($ue);

        return $ordreMax[0]['ordreMax'] === null ? 1 : ++$ordreMax[0]['ordreMax'];
    }

//    public function deplaceSae(ApcSae $apcSae, int $position)
//    {
//        //modifie l'ordre de la ressource
//        $ordreInitial = $apcSae->getOrdre();
//
//        //récupère toutes les ressources à déplacer
//        return $this->inverse($ordreInitial, $ordreInitial + $position, $apcSae);
//    }
//
//    private function inverse(?int $ordreInitial, ?int $ordreDestination, ApcSae $apcSae): bool
//    {
//        $sae = $this->apcSaeRepository->findOneBy([
//            'ordre' => $ordreDestination,
//            'semestre' => $apcSae->getSemestre()->getId()
//        ]);
//        $apcSae->setOrdre($ordreDestination);
//
//        if ($sae !== null) {
//            $sae->setOrdre($ordreInitial);
//        }
//
//        $this->entityManager->flush();
//
//        return true;
//    }
}
