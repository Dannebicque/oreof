<?php

namespace App\Controller;

use App\Repository\AnneeUniversitaireRepository;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends BaseController
{
    #[Route('/export', name: 'app_export_index')]
    public function index(
        AnneeUniversitaireRepository $anneeUniversitaireRepository,
        ComposanteRepository $composanteRepository,

    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SES');

        return $this->render('export/index.html.twig', [
            'annees' => $anneeUniversitaireRepository->findAll(),
            'composantes' => $composanteRepository->findAll(),
        ]);
    }

    #[Route('/export/liste', name: 'app_export_liste')]
    public function liste(
        ComposanteRepository $composanteRepository,
        FormationRepository $formationRepository,
        Request $request
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SES');

        $composante = $composanteRepository->find($request->query->get('composante'));

        if (!$composante) {
            throw $this->createNotFoundException('La composante n\'existe pas');
        }

        $formations = $formationRepository->findByComposante($composante, $this->getAnneeUniversitaire());

        return $this->render('export/_liste.html.twig', [
            'formations' => $formations
        ]);
    }
}
