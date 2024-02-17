<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/DefaultController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/02/2023 16:34
 */

namespace App\Controller;

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
}
