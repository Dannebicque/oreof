<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/DefaultController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/02/2023 16:34
 */

namespace App\Controller;

use App\Classes\CalculStructureParcours;
use App\Classes\GetFormations;
use App\DTO\StatsFichesMatieres;
use App\Repository\ComposanteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(
        Request $request
    ): Response {
        return $this->render(
            'default/index.html.twig',
            [
                'step' => $request->query->get('step', 'formation'),
            ]
        );
    }

    #[Route('/wizard', name: 'app_homepage_wizard')]
    public function wizard(
        ComposanteRepository $composanteRepository,
        Request $request
    ): Response {
        $step = $request->query->get('step', 'formation');

        switch ($step) {
            case 'formation':
                return $this->render(
                    'default/_formation.html.twig',
                    [
                        'step' => $step,
                    ]
                );
            case 'fiche':
                if ($this->isGranted('ROLE_SES')) {
                    return $this->render(
                        'default/_fichesSes.html.twig',
                        [
                            'step' => $step,
                            'composantes' => $composanteRepository->findPorteuse(),
                        ]
                    );
                }
                return $this->render(
                    'default/_fiches.html.twig',
                    [
                        'step' => $step,
                    ]
                );
            case 'cfvu':
                return $this->render(
                    'default/_cfvu.html.twig',
                    [
                        'step' => $step,
                    ]
                );
            default:
                return $this->render(
                    'default/_formation.html.twig',
                    [
                        'step' => $step,
                    ]
                );
        }
    }

    #[Route('/ses/fiches-composante', name: 'app_fiches_composantes')]
    public function fichesComposante(
        ComposanteRepository   $composanteRepository,
        CalculStructureParcours $calculStructureParcours,
        GetFormations         $getFormations,
        Request               $request,
    ): Response {
        $composante = $composanteRepository->find($request->query->get('value'));

        if ($composante === null) {
            throw $this->createNotFoundException('La composante n\'existe pas');
        }

        $tFormations = $composante->getFormations();

        $stats = [];
        foreach ($tFormations as $formation) {

            $parcourss = $formation->getParcours();
            $stats[$formation->getId()]['stats'] = new StatsFichesMatieres();

            foreach ($parcourss as $parcours) {
                $stats[$formation->getId()][$parcours->getId()] = $calculStructureParcours->calcul($parcours, false, false);
                $stats[$formation->getId()]['stats']->addStatsParcours(
                    $stats[$formation->getId()][$parcours->getId()]->statsFichesMatieresParcours
                );
            }
        }

        return $this->render('default/_fiches_composante.html.twig', [
            'formations' => $tFormations,
            'stats' => $stats
        ]);

    }
}
