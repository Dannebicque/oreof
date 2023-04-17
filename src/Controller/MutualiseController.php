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
use Symfony\Component\HttpFoundation\Request;
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
    public function step1(): Response
    {
        return $this->render('mutualise_wizard/_step1.html.twig', [
        ]);
    }

    #[Route('/1/liste', name: 'fiche_matiere_liste')]
    public function step1Liste(
        Request $request,
        FicheMatiereMutualisableRepository $ficheMatiereRepository): Response
    {
        $sort = $request->query->get('sort') ?? 'libelle';
        $direction = $request->query->get('direction') ?? 'asc';
        $q = $request->query->get('q') ?? null;

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SES')) {
            //pas de filtre, toutes les UE
            $fiches = $ficheMatiereRepository->findAllBy([$sort => $direction], $q);
        } else {
            //filtre selon mes parcours
            $fiches = $ficheMatiereRepository->findByParcours($this->getUser(), [$sort => $direction], $q);
        }

        return $this->render('mutualise_wizard/_step1Liste.html.twig', [
            'fiches' => $fiches,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/2', name: 'step2')]
    public function step2(): Response
    {
        return $this->render('mutualise_wizard/_step2.html.twig', [
        ]);
    }

    #[Route('/2/liste', name: 'ue_liste')]
    public function step2Liste(
        Request $request,
        UeMutualisableRepository $ueRepository): Response
    {
        $sort = $request->query->get('sort') ?? 'libelle';
        $direction = $request->query->get('direction') ?? 'asc';
        $q = $request->query->get('q') ?? null;

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SES')) {
            //pas de filtre, toutes les UE
            $ues = $ueRepository->findAllBy([$sort => $direction], $q);
        } else {
            //filtre selon mes parcours
        }

        return $this->render('mutualise_wizard/_step2Liste.html.twig', [
            'ues' => $ues,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/3', name: 'step3')]
    public function step3(): Response
    {
        return $this->render('mutualise_wizard/_step3.html.twig', [

        ]);
    }

    #[Route('/3/liste', name: 'semestre_liste')]
    public function step3Liste(
        Request $request,
        SemestreMutualisableRepository $semestreRepository): Response
    {
        $sort = $request->query->get('sort') ?? 'libelle';
        $direction = $request->query->get('direction') ?? 'asc';
        $q = $request->query->get('q') ?? null;

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SES')) {
            //pas de filtre, toutes les UE
            $semestres = $semestreRepository->findAllBy([$sort => $direction], $q);
        } else {
            //filtre selon mes parcours
        }

        return $this->render('mutualise_wizard/_step3Liste.html.twig', [
            'semestres' => $semestres,
            'sort' => $sort,
            'direction' => $direction,
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
