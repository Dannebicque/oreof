<?php

namespace App\Controller;

use App\Entity\EcUe;
use App\Entity\ElementConstitutif;
use App\Entity\Parcours;
use App\Entity\Ue;
use App\Form\EcStep4Type;
use App\Form\ElementConstitutifType;
use App\Repository\EcUeRepository;
use App\Repository\ElementConstitutifRepository;
use App\Repository\LangueRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
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
    public function new(
        EcUeRepository $ecUeRepository,
        LangueRepository $langueRepository,
        Request $request, ElementConstitutifRepository $elementConstitutifRepository, Ue $ue): Response
    {
        $elementConstitutif = new ElementConstitutif();

        $form = $this->createForm(ElementConstitutifType::class, $elementConstitutif, [
            'action' => $this->generateUrl('app_element_constitutif_new', ['ue' => $ue->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ueEc = new EcUe($ue, $elementConstitutif);
            $ecUeRepository->save($ueEc, true);
            $langueFr = $langueRepository->findOneBy(['codeIso' => 'fr']);
            if ($langueFr !== null) {
                $elementConstitutif->addLangueDispense($langueFr);
                $langueFr->addElementConstitutif($elementConstitutif);
                $elementConstitutif->addLangueSupport($langueFr);
                $langueFr->addLanguesSupportsEc($elementConstitutif);
            }
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

    #[Route('/{id}/edit/{parcours}', name: 'app_element_constitutif_edit', methods: ['GET', 'POST'])]
    public function edit(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        ElementConstitutif $elementConstitutif, Parcours $parcours): Response
    {
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($parcours->getFormation()?->getTypeDiplome());

        return $this->render('element_constitutif/edit.html.twig', [
            'ec' => $elementConstitutif,
            'parcours' => $parcours,
            'typeDiplome' => $typeDiplome,
        ]);
    }

    #[Route('/{id}/structure-ec', name: 'app_element_constitutif_structure', methods: ['GET', 'POST'])]
    public function structureEc(ElementConstitutif $elementConstitutif): Response
    {
        if ($this->isGranted('ROLE_RESP_FORMATION')) {
            $form = $this->createForm(EcStep4Type::class, $elementConstitutif, [
                'action' => $this->generateUrl('app_element_constitutif_structure',
                    ['id' => $elementConstitutif->getId()]),
            ]);

            return $this->render('element_constitutif/_structureEc.html.twig', [
                'ec' => $elementConstitutif,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('element_constitutif/_structureEcNonEditable.html.twig', [
            'ec' => $elementConstitutif,
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
