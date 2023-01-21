<?php

namespace App\Controller\Config;

use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/site')]
class SiteController extends AbstractController
{
    #[Route('/', name: 'app_site_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('config/site/index.html.twig', [
        ]);
    }

    #[Route('/liste', name: 'app_site_liste', methods: ['GET'])]
    public function liste(SiteRepository $siteRepository): Response
    {
        return $this->render('config/site/_liste.html.twig', [
            'sites' => $siteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_site_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SiteRepository $siteRepository): Response
    {
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site, [
            'action' => $this->generateUrl('app_site_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $siteRepository->save($site, true);
            return $this->json(true);
        }

        return $this->render('config/site/new.html.twig', [
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_site_show', methods: ['GET'])]
    public function show(Site $site): Response
    {
        return $this->render('config/site/show.html.twig', [
            'site' => $site,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_site_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Site $site, SiteRepository $siteRepository): Response
    {
        $form = $this->createForm(SiteType::class, $site, [
            'action' => $this->generateUrl('app_site_edit', ['id' => $site->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $siteRepository->save($site, true);
            return $this->json(true);
        }

        return $this->render('config/site/new.html.twig', [
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/duplicate', name: 'app_site_duplicate', methods: ['GET'])]
    public function duplicate(
        SiteRepository $siteRepository,
        Site $site
    ): Response {
        $siteNew = clone $site;
        $siteNew->setLibelle($site->getLibelle() . ' - Copie');
        $siteRepository->save($siteNew, true);
        return $this->json(true);
    }

    #[Route('/{id}', name: 'app_site_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        Site $site,
        SiteRepository $siteRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $site->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf'))) {
            $siteRepository->remove($site, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
