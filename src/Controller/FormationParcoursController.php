<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FormationParcoursController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Entity\Formation;
use App\Repository\ParcoursRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormationParcoursController extends AbstractController
{
    #[Route('/formation/parcours/liste/{formation}', name: 'app_formation_liste_parcours')]
    public function liste(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        ParcoursRepository  $parcoursRepository,
        Formation $formation
    ): Response {
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome()?->getModeleMcc());
        $parcours = $parcoursRepository->findByFormation($formation);
        return $this->render('formation_parcours/_liste.html.twig', [
            'parcours' => $parcours,
            'formation' => $formation,
            'typeDiplome' => $typeDiplome
        ]);
    }
}
