<?php

namespace App\Controller\Config;

use App\Entity\TypeUe;
use App\Form\TypeUeType;
use App\Repository\TypeUeRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/type/ue')]
class TypeUeController extends AbstractController
{
    #[Route('/', name: 'app_type_ue_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('config/type_ue/index.html.twig', [
        ]);
    }

    #[Route('/liste', name: 'app_type_ue_liste', methods: ['GET'])]
    public function liste(TypeUeRepository $typeUeRepository): Response
    {
        return $this->render('config/type_ue/_liste.html.twig', [
            'type_ues' => $typeUeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_type_ue_new', methods: ['GET', 'POST'])]
    public function new(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Request $request, TypeUeRepository $typeUeRepository): Response
    {
        $typeUe = new TypeUe();
        $form = $this->createForm(TypeUeType::class, $typeUe, [
            'action' => $this->generateUrl('app_type_ue_new'),
            'typesDiplomes' => $typeDiplomeRegistry->getChoices(),

        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeUeRepository->save($typeUe, true);

            return $this->json(true);
        }

        return $this->render('config/type_ue/new.html.twig', [
            'type_ue' => $typeUe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_type_ue_show', methods: ['GET'])]
    public function show(TypeUe $typeUe): Response
    {
        return $this->render('config/type_ue/show.html.twig', [
            'type_ue' => $typeUe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_ue_edit', methods: ['GET', 'POST'])]
    public function edit(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Request $request, TypeUe $typeUe, TypeUeRepository $typeUeRepository): Response
    {
        $form = $this->createForm(TypeUeType::class, $typeUe, [
            'action' => $this->generateUrl('app_type_ue_edit', ['id' => $typeUe->getId()]),
            'typesDiplomes' => $typeDiplomeRegistry->getChoices(),

        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeUeRepository->save($typeUe, true);

            return $this->json(true);
        }

        return $this->render('config/type_ue/new.html.twig', [
            'type_ue' => $typeUe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/duplicate', name: 'app_type_ue_duplicate', methods: ['GET'])]
    public function duplicate(
        TypeUeRepository $typeUeRepository,
        TypeUe $typeUe
    ): Response {
        $typeUeNew = clone $typeUe;
        $typeUeNew->setLibelle($typeUe->getLibelle() . ' - Copie');
        $typeUeRepository->save($typeUeNew, true);
        return $this->json(true);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'app_type_ue_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        TypeUe $typeUe,
        TypeUeRepository $typeUeRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $typeUe->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf'))) {
            $typeUeRepository->remove($typeUe, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
