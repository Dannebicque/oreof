<?php

namespace App\Controller;

use App\Entity\Parcours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParcoursBccController extends AbstractController
{
    #[Route('/parcours/bcc/{parcours}', name: 'app_parcours_bcc')]
    public function index(Parcours $parcours): Response
    {
        return $this->render('parcours_bcc/index.html.twig', [
            'parcours' => $parcours,
        ]);
    }
}
