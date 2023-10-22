<?php

namespace App\Controller;

use App\Entity\Composante;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ValidationController extends AbstractController
{
    #[Route('/validation', name: 'app_validation_index')]
    public function index(): Response
    {
        return $this->render('validation/index.html.twig', [
        ]);
    }

    #[Route('/validation/composante/{composante}', name: 'app_validation_composante_index')]
    public function composante(Composante $composante): Response
    {
        return $this->render('validation/index.html.twig', [
            'composante' => $composante
        ]);
    }

    #[Route('/validation/liste', name: 'app_validation_formation_liste')]
    public function liste(): Response
    {
        return $this->render('validation/_liste.html.twig', [
        ]);
    }
}
