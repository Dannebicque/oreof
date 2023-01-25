<?php

namespace App\Controller;

use App\Entity\BlocCompetence;
use App\Entity\Formation;
use App\Form\BlocCompetenceType;
use App\Repository\BlocCompetenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/bloc/competence')]
class BlocCompetenceController extends AbstractController
{
    #[Route('/liste/{formation}', name: 'app_bloc_competence_liste', methods: ['GET'])]
    public function liste(BlocCompetenceRepository $blocCompetenceRepository, Formation $formation): Response
    {
        return $this->render('bloc_competence/_liste.html.twig', [
            'bloc_competences' => $blocCompetenceRepository->findByFormation(['formation' => $formation->getId()]),
        ]);
    }

    #[Route('/new/{formation}', name: 'app_bloc_competence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, BlocCompetenceRepository $blocCompetenceRepository, Formation $formation): Response
    {
        $blocCompetence = new BlocCompetence($formation);
        $form = $this->createForm(BlocCompetenceType::class, $blocCompetence, ['action' => $this->generateUrl('app_bloc_competence_new', ['formation' => $formation->getId()])]);
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
    public function edit(Request $request, BlocCompetence $blocCompetence, BlocCompetenceRepository $blocCompetenceRepository): Response
    {
        $form = $this->createForm(BlocCompetenceType::class, $blocCompetence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blocCompetenceRepository->save($blocCompetence, true);

            return $this->redirectToRoute('app_bloc_competence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bloc_competence/edit.html.twig', [
            'bloc_competence' => $blocCompetence,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bloc_competence_delete', methods: ['POST'])]
    public function delete(Request $request, BlocCompetence $blocCompetence, BlocCompetenceRepository $blocCompetenceRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$blocCompetence->getId(), $request->request->get('_token'))) {
            $blocCompetenceRepository->remove($blocCompetence, true);
        }

        return $this->redirectToRoute('app_bloc_competence_index', [], Response::HTTP_SEE_OTHER);
    }
}
