<?php

namespace App\Controller;

use App\Entity\BlocCompetence;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Form\BlocCompetenceType;
use App\Repository\BlocCompetenceRepository;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/bloc/competence')]
class BlocCompetenceController extends AbstractController
{
    #[Route('/liste/formation/{formation}', name: 'app_bloc_competence_liste_formation', methods: ['GET'])]
    public function listeFormation(BlocCompetenceRepository $blocCompetenceRepository, Formation $formation): Response
    {
        return $this->render('bloc_competence/_liste.html.twig', [
            'bloc_competences' => $blocCompetenceRepository->findByFormation(['formation' => $formation->getId()]),
        ]);
    }

    #[Route('/liste/parcours/{parcours}', name: 'app_bloc_competence_liste_parcours', methods: ['GET'])]
    public function listeParcours(BlocCompetenceRepository $blocCompetenceRepository, Parcours $parcours): Response
    {
        return $this->render('bloc_competence/_liste.html.twig', [
            'bloc_competences' => $blocCompetenceRepository->findByParcours(['parcours' => $parcours->getId()]),
        ]);
    }

    #[Route('/new/parcours/{parcours}', name: 'app_bloc_competence_new_parcours', methods: ['GET', 'POST'])]
    public function newParcours(
        Request $request,
        BlocCompetenceRepository $blocCompetenceRepository,
        Parcours $parcours
    ): Response {
        $blocCompetence = new BlocCompetence();
        $blocCompetence->setParcours($parcours);
        $form = $this->createForm(BlocCompetenceType::class, $blocCompetence,
            ['action' => $this->generateUrl('app_bloc_competence_new_parcours', ['parcours' => $parcours->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blocCompetenceRepository->save($blocCompetence, true);

            return $this->json(true);
        }

        return $this->render('bloc_competence/new.html.twig', [
            'bloc_competence' => $blocCompetence,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new/formation/{formation}', name: 'app_bloc_competence_new_formation', methods: ['GET', 'POST'])]
    public function newFormation(
        Request $request,
        BlocCompetenceRepository $blocCompetenceRepository,
        Formation $formation
    ): Response {
        $blocCompetence = new BlocCompetence();
        $blocCompetence->setFormation($formation);
        $form = $this->createForm(BlocCompetenceType::class, $blocCompetence, [
            'action' => $this->generateUrl('app_bloc_competence_new_formation', ['formation' => $formation->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blocCompetenceRepository->save($blocCompetence, true);

            return $this->json(true);
        }

        return $this->render('bloc_competence/new.html.twig', [
            'bloc_competence' => $blocCompetence,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_bloc_competence_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        BlocCompetence $blocCompetence,
        BlocCompetenceRepository $blocCompetenceRepository
    ): Response {
        $form = $this->createForm(BlocCompetenceType::class, $blocCompetence,
            [
                'action' => $this->generateUrl('app_bloc_competence_edit', ['id' => $blocCompetence->getId()]),
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blocCompetenceRepository->save($blocCompetence, true);

            return $this->json(true);
        }

        return $this->render('bloc_competence/new.html.twig', [
            'bloc_competence' => $blocCompetence,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/duplicate', name: 'app_bloc_competence_duplicate', methods: ['GET'])]
    public function duplicate(
        BlocCompetenceRepository $blocCompetenceRepository,
        BlocCompetence $blocCompetence
    ): Response {
        $blocCompetenceNew = clone $blocCompetence;
        $blocCompetenceNew->setLibelle($blocCompetence->getLibelle() . ' - Copie');
        $blocCompetenceRepository->save($blocCompetenceNew, true);
        return $this->json(true);
    }

    #[Route('/{id}', name: 'app_bloc_competence_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        BlocCompetence $blocCompetence,
        BlocCompetenceRepository $blocCompetenceRepository
    ): Response {
        //todo: tester s'il y a des compétences dans le bloc
        if ($this->isCsrfTokenValid('delete' . $blocCompetence->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf'))) {
            $blocCompetenceRepository->remove($blocCompetence, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
