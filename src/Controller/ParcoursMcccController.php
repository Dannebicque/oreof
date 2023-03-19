<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParcoursMcccController extends AbstractController
{
    #[Route('/parcours/mccc', name: 'app_parcours_mccc')]
    public function index(): Response
    {
        return $this->render('parcours_mccc/index.html.twig', [
            'controller_name' => 'ParcoursMcccController',
        ]);
    }
}
