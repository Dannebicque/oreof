<?php

namespace App\Controller;

use App\Entity\ElementConstitutif;
use App\Entity\Ue;
use App\Form\EcStep4Type;
use App\Form\ElementConstitutifType;
use App\Repository\ElementConstitutifRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/element/constitutif')]
class ElementConstitutifController extends AbstractController
{
    #[Route('/', name: 'app_element_constitutif_index', methods: ['GET'])]
    public function index(ElementConstitutifRepository $elementConstitutifRepository): Response
    {
        return $this->render('element_constitutif/index.html.twig', [
            'element_constitutifs' => $elementConstitutifRepository->findAll(),
        ]);
    }

    #[Route('/new/{ue}', name: 'app_element_constitutif_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ElementConstitutifRepository $elementConstitutifRepository, Ue $ue): Response
    {
        $elementConstitutif = new ElementConstitutif();
        $elementConstitutif->setUe($ue);
        $form = $this->createForm(ElementConstitutifType::class, $elementConstitutif, [
            'action' => $this->generateUrl('app_element_constitutif_new', ['ue' => $ue->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $elementConstitutifRepository->save($elementConstitutif, true);

            return $this->json(true);
        }

        return $this->render('element_constitutif/new.html.twig', [
            'element_constitutif' => $elementConstitutif,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_element_constitutif_show', methods: ['GET'])]
    public function show(ElementConstitutif $elementConstitutif): Response
    {
        return $this->render('element_constitutif/show.html.twig', [
            'element_constitutif' => $elementConstitutif,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_element_constitutif_edit', methods: ['GET', 'POST'])]
    public function edit(ElementConstitutif $elementConstitutif): Response
    {
        return $this->render('element_constitutif/edit.html.twig', [
            'ec' => $elementConstitutif,
        ]);
    }

    #[Route('/{id}/structure-ec', name: 'app_element_constitutif_structure', methods: ['GET', 'POST'])]
    public function structureEc(ElementConstitutif $elementConstitutif): Response
    {
        $form = $this->createForm(EcStep4Type::class, $elementConstitutif, [
            'action' => $this->generateUrl('app_element_constitutif_structure', ['id' => $elementConstitutif->getId()]),
        ]);

        return $this->render('element_constitutif/structureEc.html.twig', [
            'ec' => $elementConstitutif,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_element_constitutif_delete', methods: ['POST'])]
    public function delete(Request $request, ElementConstitutif $elementConstitutif, ElementConstitutifRepository $elementConstitutifRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$elementConstitutif->getId(), $request->request->get('_token'))) {
            $elementConstitutifRepository->remove($elementConstitutif, true);
        }

        return $this->redirectToRoute('app_element_constitutif_index', [], Response::HTTP_SEE_OTHER);
    }
}
