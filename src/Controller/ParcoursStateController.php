<?php

namespace App\Controller;

use App\Classes\verif\ParcoursValide;
use App\Entity\Parcours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ParcoursStateController extends AbstractController
{
    #[Route('/parcours/state/{parcours}', name: 'app_parcours_state')]
    public function index(Parcours $parcours): Response
    {
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();
        $valideParcours = new ParcoursValide($parcours, $typeDiplome);

        return $this->render('parcours_state/_index.html.twig', [
            'parcours' => $parcours,
            'valide' => $valideParcours->valideParcours(),
            'typeDiplome' => $typeDiplome,
        ]);
    }
}
