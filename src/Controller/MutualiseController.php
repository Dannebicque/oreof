<?php

namespace App\Controller;

use App\Repository\BlocCompetenceRepository;
use App\Repository\FicheMatiereMutualisableRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\SemestreMutualisableRepository;
use App\Repository\SemestreRepository;
use App\Repository\UeMutualisableRepository;
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
    public function step1(FicheMatiereMutualisableRepository $ficheMatiereRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SES')) {
            //pas de filtre, toutes les UE
            $fiches = $ficheMatiereRepository->findAll();
        } else {
            //filtre selon mes parcours
            $fiches = $ficheMatiereRepository->findByParcours($this->getUser());
        }

        return $this->render('mutualise_wizard/_step1.html.twig', [
            'fiches' => $fiches
        ]);
    }

    #[Route('/2', name: 'step2')]
    public function step2(UeMutualisableRepository $ueRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SES')) {
            //pas de filtre, toutes les UE
            $ues = $ueRepository->findAll();
        } else {
            //filtre selon mes parcours
        }

        return $this->render('mutualise_wizard/_step2.html.twig', [
            'ues' => $ues
        ]);
    }

    #[Route('/3', name: 'step3')]
    public function step3(SemestreMutualisableRepository $semestreRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SES')) {
            //pas de filtre, toutes les UE
            $semestres = $semestreRepository->findAll();
        } else {
            //filtre selon mes parcours
        }

        return $this->render('mutualise_wizard/_step3.html.twig', [
            'semestres' => $semestres
        ]);
    }

    #[Route('/4', name: 'step4')]
    public function step4(BlocCompetenceRepository $blocCompetenceRepository): Response
    {
        $bccs = $blocCompetenceRepository->findBy(['parcours' => null]);

        return $this->render('mutualise_wizard/_step4.html.twig', [
            'bloc_competences' => $bccs
        ]);
    }
}
