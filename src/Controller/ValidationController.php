<?php

namespace App\Controller;

use App\Classes\ValidationProcess;
use App\Entity\Composante;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ValidationController extends BaseController
{
    #[Route('/validation', name: 'app_validation_index')]
    public function index(
        ValidationProcess    $validationProcess,
        ComposanteRepository $composanteRepository,
    ): Response {
        //todo:  Affichage des bons boutons selon le process et le status choisi sur la liste en bas et haut de page.
        return $this->render('validation/index.html.twig', [
            'ses' => true,
            'composantes' => $composanteRepository->findAll(),
            'types_validation' => $validationProcess->getProcess(),
        ]);
    }

    #[Route('/validation/composante/{composante}', name: 'app_validation_composante_index')]
    public function composante(
        ComposanteRepository $composanteRepository,
        Composante           $composante
    ): Response {
        return $this->render('validation/index.html.twig', [
            'composante' => $composante,
            'composantes' => $composanteRepository->findAll(),

        ]);
    }

    #[Route('/validation/liste', name: 'app_validation_formation_liste')]
    public function liste(
        ValidationProcess    $validationProcess,
        ComposanteRepository $composanteRepository,
        FormationRepository  $formationRepository,
        Request              $request
    ): Response {
        $typeValidation = $request->query->get('typeValidation');
        $process = $validationProcess->getEtape($typeValidation);

        if ($request->query->has('composante')) {
            if ($request->query->get('composante') === 'all') {
                $composante = null;
                $formations = $formationRepository->findByTypeValidation($this->getAnneeUniversitaire(), $process['transition']);
            } else {
                $composante = $composanteRepository->find($request->query->get('composante'));
                if (!$composante) {
                    throw $this->createNotFoundException('La composante n\'existe pas');
                }
                $formations = $formationRepository->findByComposanteTypeValidation($composante, $this->getAnneeUniversitaire(), $process['transition']);
            }




        } else {
            $formations = [];
            $process = null;
        }

        $composantes = $composanteRepository->findAll();
        return $this->render('validation/_liste.html.twig', [
            'process' => $process,
            'formations' => $formations,
            'composantes' => $composantes,
            'etape' => $typeValidation ?? null,
        ]);
    }
}
