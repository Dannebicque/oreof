<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Config/TypeDiplomeController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Config;

use App\Entity\TypeDiplome;
use App\Form\TypeDiplomeType;
use App\Repository\TypeDiplomeRepository;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/administration/type-diplome')]
class TypeDiplomeController extends AbstractController
{
    #[Route('/', name: 'app_type_diplome_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('config/type_diplome/index.html.twig');
    }

    #[Route('/liste', name: 'app_type_diplome_liste', methods: ['GET'])]
    public function liste(TypeDiplomeRepository $typeDiplomeRepository): Response
    {
        return $this->render('config/type_diplome/_liste.html.twig', [
            'type_diplomes' => $typeDiplomeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_type_diplome_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        TypeDiplomeRepository $typeDiplomeRepository
    ): Response
    {
        $typeDiplome = new TypeDiplome();
        $form = $this->createForm(TypeDiplomeType::class, $typeDiplome, [
            'action' => $this->generateUrl('app_type_diplome_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeDiplomeRepository->save($typeDiplome, true);
            // Abandon de la fenêtre modale
            // return $this->json(true);

            $this->addFlash('toast', [
                'type' => 'success',
                'text' => 'Création du type de diplôme réussie',
                'title' => 'Succès',
            ]);
            return $this->redirectToRoute('app_type_diplome_index');
        }

        return $this->render('config/type_diplome/new.html.twig', [
            'type_diplome' => $typeDiplome,
            'form' => $form->createView(),
            'titre' => "Création d'un type de diplôme"
        ]);
    }

    #[Route('/{id}', name: 'app_type_diplome_show', methods: ['GET'])]
    public function show(TypeDiplome $typeDiplome): Response
    {
        return $this->render('config/type_diplome/show.html.twig', [
            'type_diplome' => $typeDiplome,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_diplome_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        TypeDiplome $typeDiplome,
        TypeDiplomeRepository $typeDiplomeRepository
    ): Response
    {
        $form = $this->createForm(TypeDiplomeType::class, $typeDiplome, [
            'action' => $this->generateUrl('app_type_diplome_edit', ['id' => $typeDiplome->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeDiplomeRepository->save($typeDiplome, true);
            // Abandon de la fenêtre modale
            // return $this->json(true);
            $this->addFlash('toast', [
                'type' => 'success',
                'text' => 'Type Diplôme modifié avec succès',
                'title' => 'Succès',
            ]);
            return $this->redirectToRoute('app_type_diplome_index');
        }

        return $this->render('config/type_diplome/new.html.twig', [
            'type_diplome' => $typeDiplome,
            'form' => $form->createView(),
            'titre' => "Modification d'un type de diplôme"
        ]);
    }

    #[Route('/{id}/duplicate', name: 'app_type_diplome_duplicate', methods: ['GET'])]
    public function duplicate(
        TypeDiplomeRepository $typeDiplomeRepository,
        TypeDiplome $typeDiplome
    ): Response {
        $typeDiplomeNew = clone $typeDiplome;
        $typeDiplomeNew->setLibelle($typeDiplome->getLibelle() . ' - Copie');
        $typeDiplomeRepository->save($typeDiplomeNew, true);
        return $this->json(true);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'app_type_diplome_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        TypeDiplome $typeDiplome,
        TypeDiplomeRepository $typeDiplomeRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $typeDiplome->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            $typeDiplomeRepository->remove($typeDiplome, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
