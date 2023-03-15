<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Form\ParcoursType;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/parcours')]
class ParcoursController extends AbstractController
{
    #[Route('/', name: 'app_parcours_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('parcours/index.html.twig');
    }

    #[Route('/liste', name: 'app_parcours_liste', methods: ['GET'])]
    public function liste(
        ParcoursRepository $parcoursRepository,
        Request $request
    ): Response
    {
        $sort = $request->query->get('sort') ?? 'typeDiplome';
        $direction = $request->query->get('direction') ?? 'asc';
        $q = $request->query->get('q') ?? null;

        return $this->render('parcours/_liste.html.twig', [
            'parcours' => $parcoursRepository->findAll(),
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

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
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($parcour->getFormation()?->getTypeDiplome());

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
    public function delete(
        EntityManagerInterface $entityManager,
        Request $request, Parcours $parcour, ParcoursRepository $parcoursRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $parcour->getId(), JsonRequest::getValueFromRequest($request, 'csrf'))) {
            foreach ($parcour->getSemestreParcours() as $sp) {
                $entityManager->remove($sp);
            }

            $parcoursRepository->remove($parcour, true);

            //todo: supprimer semestres, UE ? (ou les liens, attention si EC mutualisé ou sur semestres communs
            return $this->json(true);
        }

        return $this->json(false);
    }
}
