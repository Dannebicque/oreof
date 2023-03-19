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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormationParcoursController extends AbstractController
{
    #[Route('/formation/parcours/liste/{formation}', name: 'app_formation_liste_parcours')]
    public function liste(
        ParcoursRepository  $parcoursRepository,
        Formation $formation
    ): Response {
        $parcours = $parcoursRepository->findByFormation($formation);
        return $this->render('formation_parcours/_liste.html.twig', [
            'parcours' => $parcours,
            'formation' => $formation
        ]);
    }
}
