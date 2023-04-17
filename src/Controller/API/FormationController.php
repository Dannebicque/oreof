<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/API/FormationController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\API;

use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormationController extends AbstractController
{
    #[Route('/api/formation', name: 'api_formation')]
    public function getFormation(
        FormationRepository $formationRepository,
    ): Response {
        $formations = $formationRepository->findAll();
        $t = [];
        foreach ($formations as $formation) {
            $t[] = [
                'id' => $formation->getId(),
                'libelle' => $formation->getDisplay(),
            ];
        }
        return $this->json($t);
    }
}
