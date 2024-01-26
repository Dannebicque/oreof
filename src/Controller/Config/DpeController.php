<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Config/AnneeUniversitaireController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Config;

use App\Entity\Dpe;
use App\Form\DpeType;
use App\Repository\DpeRepository;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dpe')]
class DpeController extends AbstractController
{
    #[Route('/', name: 'app_dpe_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('config/dpe/index.html.twig');
    }

    #[Route('/liste', name: 'app_dpe_liste', methods: ['GET'])]
    public function liste(DpeRepository $dpeRepository): Response
    {
        return $this->render('config/dpe/_liste.html.twig', [
            'dpes' => $dpeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_dpe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DpeRepository $dpeRepository): Response
    {
        $dpe = new Dpe();
        $form = $this->createForm(
            DpeType::class,
            $dpe,
            ['action' => $this->generateUrl('app_dpe_new')]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dpeRepository->save($dpe, true);

            return $this->json(true);
        }

        return $this->render('config/dpe/new.html.twig', [
            'dpe' => $dpe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_dpe_show', methods: ['GET'])]
    public function show(Dpe $dpe): Response
    {
        return $this->render('config/dpe/show.html.twig', [
            'dpe' => $dpe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_dpe_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request                      $request,
        Dpe                          $dpe,
        DpeRepository $dpeRepository
    ): Response {
        $form = $this->createForm(
            DpeType::class,
            $dpe,
            [
                'action' => $this->generateUrl('app_dpe_edit', [
                    'id' => $dpe->getId()
                ])
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dpeRepository->save($dpe, true);

            return $this->json(true);
        }

        return $this->render('config/dpe/new.html.twig', [
            'dpe' => $dpe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/duplicate', name: 'app_dpe_duplicate', methods: ['GET'])]
    public function duplicate(
        DpeRepository $dpeRepository,
        Dpe           $dpe
    ): Response {
        $dpeNew = clone $dpe;
        $dpeNew->setLibelle($dpe->getLibelle() . ' - Copie');
        $dpeRepository->save($dpeNew, true);
        return $this->json(true);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'app_dpe_delete', methods: ['DELETE'])]
    public function delete(
        Request                      $request,
        Dpe                          $dpe,
        DpeRepository $dpeRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $dpe->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            $dpeRepository->remove($dpe, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
