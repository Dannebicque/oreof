<?php

namespace App\Controller;

use App\Entity\Parcours;
use App\Service\VersioningParcours;
use App\Service\VersioningStructure;
use App\TypeDiplome\Source\LicenceTypeDiplome;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StructureShowController extends AbstractController
{
    #[Route('/structure/show/licence', name: 'app_structure_show_licence')]
    public function licence(
        VersioningParcours $versioningParcours,
        LicenceTypeDiplome $typeD,
        Parcours $parcours,
        bool $hasLastVersion = false
    ): Response {
        $dto = $typeD->calculStructureParcours($parcours, true, false);
        $structureDifferencesParcours = $versioningParcours->getStructureDifferencesBetweenParcoursAndLastVersion($parcours);
        if ($structureDifferencesParcours !== null) {
            $diffStructure = (new VersioningStructure($structureDifferencesParcours, $dto))->calculDiff();
        }

        return $this->render('typeDiplome/formation/_structure.html.twig', [
             'parcours' => $parcours,
            'diffStructure' => $diffStructure ?? null,
            'dto' => $dto,
            'hasLastVersion' => $hasLastVersion,
         ]);
    }
}
