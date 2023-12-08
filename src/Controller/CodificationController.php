<?php

namespace App\Controller;

use App\Classes\Codification\CodificationFormation;
use App\Entity\Formation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CodificationController extends AbstractController
{
    #[Route('/codification/{formation}', name: 'app_codification')]
    public function index(
        CodificationFormation $codificationFormation,
        Formation $formation): Response
    {
        $codificationFormation->setCodificationFormation($formation);

        return $this->redirectToRoute('app_formation_show', ['slug' => $formation->getSlug()]);
    }
}
