<?php

namespace App\Controller\Config;

use App\Entity\Composante;
use App\Form\ComposanteType;
use App\Repository\ComposanteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/composante')]
class ComposanteController extends AbstractController
{
    #[Route('/', name: 'app_composante_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('config/composante/index.html.twig', [
        ]);
    }

    #[Route('/liste', name: 'app_composante_liste', methods: ['GET'])]
    public function liste(ComposanteRepository $composanteRepository): Response
    {
        return $this->render('config/composante/_liste.html.twig', [
            'composantes' => $composanteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_composante_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ComposanteRepository $composanteRepository): Response
    {
        $composante = new Composante();
        $form = $this->createForm(ComposanteType::class, $composante, [
            'action' => $this->generateUrl('app_composante_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $composanteRepository->save($composante, true);

            return $this->json(true);
        }

        return $this->render('config/composante/new.html.twig', [
            'composante' => $composante,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_composante_show', methods: ['GET'])]
    public function show(Composante $composante): Response
    {
        return $this->render('config/composante/show.html.twig', [
            'composante' => $composante,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_composante_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Composante $composante, ComposanteRepository $composanteRepository): Response
    {
        $form = $this->createForm(ComposanteType::class, $composante, [
            'action' => $this->generateUrl('app_composante_edit', ['id' => $composante->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $composanteRepository->save($composante, true);

            return $this->json(true);
        }

        return $this->render('config/composante/new.html.twig', [
            'composante' => $composante,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_composante_delete', methods: ['POST'])]
    public function delete(Request $request, Composante $composante, ComposanteRepository $composanteRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$composante->getId(), $request->request->get('_token'))) {
            $composanteRepository->remove($composante, true);
        }

        return $this->redirectToRoute('app_composante_index', [], Response::HTTP_SEE_OTHER);
    }
}
