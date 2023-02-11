<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Form\ParcoursType;
use App\Repository\ParcoursRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/parcours')]
class ParcoursController extends AbstractController
{
    #[Route('/new/{formation}', name: 'app_parcours_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        ParcoursRepository $parcoursRepository,
        Formation $formation
    ): Response {
        $parcour = new Parcours($formation);
        $form = $this->createForm(ParcoursType::class, $parcour, [
            'action' => $this->generateUrl('app_parcours_new', [
                'formation' => $formation->getId(),
            ]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parcoursRepository->save($parcour, true);

            return $this->json(true);
        }

        return $this->render('parcours/new.html.twig', [
            'parcour' => $parcour,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/modal/{parcours}', name: 'app_parcours_edit_modal', methods: ['GET', 'POST'])]
    public function editModal(
        Request $request,
        ParcoursRepository $parcoursRepository,
        Parcours $parcours
    ): Response {
        $form = $this->createForm(ParcoursType::class, $parcours, [
            'action' => $this->generateUrl('app_parcours_edit_modal', [
                'parcours' => $parcours->getId(),
            ]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parcoursRepository->save($parcours, true);

            return $this->json(true);
        }

        return $this->render('parcours/new.html.twig', [
            'parcour' => $parcours,
            'form' => $form->createView(),
        ]);
    }

//    #[Route('/{id}', name: 'app_parcours_show', methods: ['GET'])]
//    public function show(Parcours $parcour): Response
//    {
//        return $this->render('parcours/show.html.twig', [
//            'parcour' => $parcour,
//        ]);
//    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}/edit', name: 'app_parcours_edit', methods: ['GET', 'POST'])]
    public function edit(Parcours $parcour, TypeDiplomeRegistry $typeDiplomeRegistry): Response
    {
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($parcour->getFormation()->getTypeDiplome());

        return $this->render('parcours/edit.html.twig', [
            'parcours' => $parcour,
            'typeDiplome' => $typeDiplome,
            'formation' => $parcour->getFormation(),
        ]);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'app_parcours_delete', methods: ['DELETE'])]
    public function delete(Request $request, Parcours $parcour, ParcoursRepository $parcoursRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $parcour->getId(), JsonRequest::getValueFromRequest($request, 'csrf'))) {
            $parcoursRepository->remove($parcour, true);
            return $this->json(true);
        }

        return $this->json(false);
    }
}
