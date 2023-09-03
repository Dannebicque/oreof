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
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_SES');

        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome()->getModeleMcc());
        $state = $typeDiplome->synchroniser($formation);

        if ($state) {
            $this->addFlash('success', 'La synchronisation a été effectuée avec succès.');
        } else {
            $this->addFlash('danger', 'La synchronisation a échoué.');
        }

        return $this->redirectToRoute('app_formation_edit', [
            'slug' => $formation->getSlug(),
        ]);
    }

    #[Route('/formation/synchronisation-mccc/{formation}', name: 'app_formation_synchronisation_mccc')]
    public function synchronisationMccc(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Formation $formation
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_SES');

        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome()->getModeleMcc());
        $state = $typeDiplome->synchroniserMccc($formation);

        if ($state) {
            $this->addFlash('success', 'La synchronisation a été effectuée avec succès.');
        } else {
            $this->addFlash('danger', 'La synchronisation a échoué.');
        }

        return $this->redirectToRoute('app_formation_edit', [
            'slug' => $formation->getSlug(),
        ]);
    }
}
