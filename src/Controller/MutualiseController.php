<?php

namespace App\Controller;

use App\Repository\FicheMatiereRepository;
use App\Repository\SemestreRepository;
use App\Repository\UeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mutualise', name: 'app_mutualise_')]
class MutualiseController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('mutualise/index.html.twig', [
        ]);
    }

    #[Route('/1', name: 'step1')]
    public function step1(FicheMatiereRepository $ficheMatiereRepository): Response
    {
        //fiches matières mutualisées avec les éléments dont je suis responsable
        return $this->render('mutualise_wizard/_step1.html.twig', [

        ]);
    }

    #[Route('/2', name: 'step2')]
    public function step2(UeRepository $ueRepository): Response
    {
        //UE mutualisées avec les éléments dont je suis responsable
        return $this->render('mutualise_wizard/_step2.html.twig', [

        ]);
    }

    #[Route('/3', name: 'step3')]
    public function step3(SemestreRepository $semestreRepository): Response
    {
        //semestres mutualisés avec les éléments dont je suis responsable
        return $this->render('mutualise_wizard/_step3.html.twig', [

        ]);
    }
}
