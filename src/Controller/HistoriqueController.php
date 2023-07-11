<?php

namespace App\Controller;

use App\Entity\Formation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoriqueController extends AbstractController
{
    #[Route('/historique/formation/{formation}', name: 'app_historique_formation')]
    public function formation(Formation $formation): Response
    {
        return $this->render('historique/formation.html.twig', [
            'historiques' => $formation->getHistoriqueFormations(),
            'formation' => $formation
        ]);
    }
}
