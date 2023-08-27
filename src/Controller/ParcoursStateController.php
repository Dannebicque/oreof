<?php

namespace App\Controller;

use App\Classes\verif\ParcoursValide;
use App\Entity\Parcours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParcoursStateController extends AbstractController
{
    #[Route('/parcours/state/{parcours}', name: 'app_parcours_state')]
    public function index(Parcours $parcours): Response
    {
        $valideParcours = new ParcoursValide();
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();



        return $this->render('parcours_state/_index.html.twig', [
            'parcours' => $parcours,
            'valide' => $valideParcours->valide($parcours, $typeDiplome),
            'typeDiplome' => $typeDiplome,
        ]);
    }
}
