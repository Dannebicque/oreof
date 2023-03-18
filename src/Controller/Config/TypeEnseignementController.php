<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Config/TypeEnseignementController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Config;

use App\Entity\TypeEnseignement;
use App\Form\TypeEnseignementType;
use App\Repository\TypeEnseignementRepository;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/type/enseignement')]
class TypeEnseignementController extends AbstractController
{
    #[Route('/', name: 'app_type_enseignement_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('config/type_enseignement/index.html.twig');
    }

    #[Route('/liste', name: 'app_type_enseignement_liste', methods: ['GET'])]
    public function liste(TypeEnseignementRepository $typeEnseignementRepository): Response
    {
        return $this->render('config/type_enseignement/_liste.html.twig', [
            'type_enseignements' => $typeEnseignementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_type_enseignement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TypeEnseignementRepository $typeEnseignementRepository): Response
    {
        $typeEnseignement = new TypeEnseignement();
        $form = $this->createForm(TypeEnseignementType::class, $typeEnseignement, [
            'action' => $this->generateUrl('app_type_enseignement_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeEnseignementRepository->save($typeEnseignement, true);

            return $this->json(true);
        }

        return $this->render('config/type_enseignement/new.html.twig', [
            'type_enseignement' => $typeEnseignement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_type_enseignement_show', methods: ['GET'])]
    public function show(TypeEnseignement $typeEnseignement): Response
    {
        return $this->render('config/type_enseignement/show.html.twig', [
            'type_enseignement' => $typeEnseignement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_enseignement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeEnseignement $typeEnseignement, TypeEnseignementRepository $typeEnseignementRepository): Response
    {
        $form = $this->createForm(TypeEnseignementType::class, $typeEnseignement, [
            'action' => $this->generateUrl('app_type_enseignement_edit', ['id' => $typeEnseignement->getId()]),

        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeEnseignementRepository->save($typeEnseignement, true);

            return $this->json(true);
        }

        return $this->render('config/type_enseignement/new.html.twig', [
            'type_enseignement' => $typeEnseignement,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}/duplicate', name: 'app_type_enseignement_duplicate', methods: ['GET'])]
    public function duplicate(
        TypeEnseignementRepository $typeEnseignementRepository,
        TypeEnseignement $typeEnseignement
    ): Response {
        $typeEnseignementNew = clone $typeEnseignement;
        $typeEnseignementNew->setLibelle($typeEnseignement->getLibelle() . ' - Copie');
        $typeEnseignementRepository->save($typeEnseignementNew, true);
        return $this->json(true);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'app_type_enseignement_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        TypeEnseignement $typeEnseignement,
        TypeEnseignementRepository $typeEnseignementRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $typeEnseignement->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            $typeEnseignementRepository->remove($typeEnseignement, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
