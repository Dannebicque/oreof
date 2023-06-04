<?php

namespace App\Controller;

use App\Entity\Formation;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormationSynchronisationController extends AbstractController
{
    #[Route('/formation/synchronisation/{formation}', name: 'app_formation_synchronisation')]
    public function index(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Formation $formation
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SES');

        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome()->getModeleMcc());
        $state = $typeDiplome->synchroniser($formation);

        return $this->render(
            'formation_synchronisation/index.html.twig', [
                'formation' => $formation,
                'state' => $state
            ]
        );
    }
}
