<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Config/MentionController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Config;

use App\Entity\Mention;
use App\Form\MentionType;
use App\Repository\MentionRepository;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mention')]
class MentionController extends AbstractController
{
    #[Route('/', name: 'app_mention_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('config/mention/index.html.twig');
    }

    #[Route('/liste', name: 'app_mention_liste', methods: ['GET'])]
    public function liste(MentionRepository $mentionRepository): Response
    {
        return $this->render('config/mention/_liste.html.twig', [
            'mentions' => $mentionRepository->findBy([], ['typeDiplome' => 'ASC', 'libelle' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_mention_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        MentionRepository $mentionRepository
    ): Response
    {
        $mention = new Mention();
        $form = $this->createForm(MentionType::class, $mention, [
            'action' => $this->generateUrl('app_mention_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mentionRepository->save($mention, true);
            return $this->json(true);
        }

        return $this->render('config/mention/new.html.twig', [
            'mention' => $mention,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_mention_show', methods: ['GET'])]
    public function show(Mention $mention): Response
    {
        return $this->render('config/mention/show.html.twig', [
            'mention' => $mention,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_mention_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Mention $mention,
        MentionRepository $mentionRepository
    ): Response
    {
        $form = $this->createForm(MentionType::class, $mention, [
            'action' => $this->generateUrl('app_mention_edit', ['id' => $mention->getId()]),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mentionRepository->save($mention, true);

            return $this->json(true);
        }

        return $this->render('config/mention/new.html.twig', [
            'mention' => $mention,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/duplicate', name: 'app_mention_duplicate', methods: ['GET'])]
    public function duplicate(
        MentionRepository $mentionRepository,
        Mention $mention
    ): Response {
        $mentionNew = clone $mention;
        $mentionNew->setLibelle($mention->getLibelle() . ' - Copie');
        $mentionRepository->save($mentionNew, true);
        return $this->json(true);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'app_mention_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        Mention $mention,
        MentionRepository $mentionRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $mention->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            $mentionRepository->remove($mention, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
