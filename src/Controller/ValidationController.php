<?php

namespace App\Controller;

use App\Entity\Composante;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ValidationController extends BaseController
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
    public function liste(
        ComposanteRepository $composanteRepository,
        FormationRepository  $formationRepository,
        Request              $request
    ): Response {
        $composante = $composanteRepository->find($request->query->get('composante'));

        if (!$composante) {
            throw $this->createNotFoundException('La composante n\'existe pas');
        }

        $formations = $formationRepository->findByComposante($composante, $this->getAnneeUniversitaire());
        return $this->render('validation/_liste.html.twig', [
            'formations' => $formations
        ]);
    }
}
