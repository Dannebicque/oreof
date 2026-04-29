<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file //wsl.localhost/Ubuntu/home/louca/oreof-stack/oreofv2/src/Controller/HelpAdminController.php
 * @author louca
 * @project oreofv2
 * @lastUpdate 29/04/2026 15:18
 */


namespace App\Controller;

use App\Entity\Help;
use App\Form\HelpType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/administration/help')] // <-- Changement ici
class HelpAdminController extends AbstractController
{
    #[Route('/', name: 'app_help_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $helps = $em->getRepository(Help::class)->findAll();
        return $this->render('help_admin/index.html.twig', ['helps' => $helps]);
    }

    #[Route('/new', name: 'app_help_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $help = new Help();
        // Le traitement du formulaire est fait via le Live Component,
        // mais on réceptionne la requête classique ici lors du clic sur "Enregistrer"
        $form = $this->createForm(\App\Form\HelpType::class, $help);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($help);
            $em->flush();
            $this->addFlash('success', 'Aide créée avec succès !');
            return $this->redirectToRoute('app_help_index');
        }

        return $this->render('help_admin/form.html.twig', ['help' => $help]);
    }

    #[Route('/{id}/edit', name: 'app_help_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Help $help, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(\App\Form\HelpType::class, $help);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Aide mise à jour !');
            return $this->redirectToRoute('app_help_index');
        }

        return $this->render('help_admin/form.html.twig', ['help' => $help]);
    }

    #[Route('/{id}', name: 'app_help_delete', methods: ['POST'])]
    public function delete(Request $request, Help $help, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$help->getId(), $request->request->get('_token'))) {
            $em->remove($help);
            $em->flush();
            $this->addFlash('success', 'Aide supprimée.');
        }
        return $this->redirectToRoute('app_help_index');
    }
}