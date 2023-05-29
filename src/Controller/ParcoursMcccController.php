<?php

namespace App\Controller;

use App\Entity\Parcours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParcoursMcccController extends AbstractController
{
    #[Route('/parcours/mccc/{parcours}', name: 'app_parcours_mccc')]
    public function index(Parcours $parcours): Response
    {
        return $this->render('parcours_mccc/index.html.twig', [
            'parcours' => $parcours
        ]);
    }
}
