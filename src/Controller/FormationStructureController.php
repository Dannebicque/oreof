<?php

namespace App\Controller;

use App\Classes\FormationStructure;
use App\Entity\Formation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormationStructureController extends BaseController
{
    #[Route('/formation/structure/{formation}', name: 'app_formation_genere_structure')]
    public function index(
        FormationStructure $formationStructure,
        Formation $formation
    ): Response
    {
        $formationStructure->genereStructre($formation);

        $this->addFlashBag('success', 'La structure de la formation a été générée');
        return $this->redirectToRoute('app_formation_edit', ['id' => $formation->getId()]);
    }
}
