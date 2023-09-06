<?php

namespace App\Controller;

use App\Entity\Composante;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComposanteController extends AbstractController
{
    #[Route('/composante/{composante}', name: 'app_composante')]
    public function index(Composante $composante): Response
    {
        return $this->render('composante/index.html.twig', [
            'composante' => $composante,
        ]);
    }
}
