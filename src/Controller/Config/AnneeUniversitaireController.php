<?php

namespace App\Controller\Config;

use App\Entity\AnneeUniversitaire;
use App\Form\AnneeUniversitaireType;
use App\Repository\AnneeUniversitaireRepository;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/annee/universitaire')]
class AnneeUniversitaireController extends AbstractController
{
    #[Route('/', name: 'app_annee_universitaire_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('config/annee_universitaire/index.html.twig');
    }

    #[Route('/liste', name: 'app_annee_universitaire_liste', methods: ['GET'])]
    public function liste(AnneeUniversitaireRepository $anneeUniversitaireRepository): Response
    {
        return $this->render('config/annee_universitaire/_liste.html.twig', [
            'annee_universitaires' => $anneeUniversitaireRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_annee_universitaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AnneeUniversitaireRepository $anneeUniversitaireRepository): Response
    {
        $anneeUniversitaire = new AnneeUniversitaire();
        $form = $this->createForm(AnneeUniversitaireType::class, $anneeUniversitaire,
            ['action' => $this->generateUrl('app_annee_universitaire_new')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $anneeUniversitaireRepository->save($anneeUniversitaire, true);

            return $this->json(true);
        }

        return $this->render('config/annee_universitaire/new.html.twig', [
            'annee_universitaire' => $anneeUniversitaire,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_annee_universitaire_show', methods: ['GET'])]
    public function show(AnneeUniversitaire $anneeUniversitaire): Response
    {
        return $this->render('config/annee_universitaire/show.html.twig', [
            'annee_universitaire' => $anneeUniversitaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_annee_universitaire_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        AnneeUniversitaire $anneeUniversitaire,
        AnneeUniversitaireRepository $anneeUniversitaireRepository
    ): Response {
        $form = $this->createForm(AnneeUniversitaireType::class, $anneeUniversitaire,
            [
                'action' => $this->generateUrl('app_annee_universitaire_edit', [
                    'id' => $anneeUniversitaire->getId()
                ])
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $anneeUniversitaireRepository->save($anneeUniversitaire, true);

            return $this->json(true);
        }

        return $this->render('config/annee_universitaire/new.html.twig', [
            'annee_universitaire' => $anneeUniversitaire,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/duplicate', name: 'app_annee_universitaire_duplicate', methods: ['GET'])]
    public function duplicate(
        AnneeUniversitaireRepository $anneeUniversitaireRepository,
        AnneeUniversitaire $anneeUniversitaire
    ): Response {
        $anneeUniversitaireNew = clone $anneeUniversitaire;
        $anneeUniversitaireNew->setLibelle($anneeUniversitaire->getLibelle() . ' - Copie');
        $anneeUniversitaireRepository->save($anneeUniversitaireNew, true);
        return $this->json(true);
    }

    #[Route('/{id}', name: 'app_annee_universitaire_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        AnneeUniversitaire $anneeUniversitaire,
        AnneeUniversitaireRepository $anneeUniversitaireRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $anneeUniversitaire->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf'))) {
            $anneeUniversitaireRepository->remove($anneeUniversitaire, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
