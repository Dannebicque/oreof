<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Form\FaqType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration/faq')]
#[IsGranted('ROLE_ADMIN')]
class FaqAdminController extends AbstractController
{
    #[Route('/', name: 'app_faq_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $faqs = $em->getRepository(Faq::class)->findBy([], ['ordre' => 'ASC', 'createdAt' => 'DESC']);

        return $this->render('faq_admin/index.html.twig', [
            'faqs' => $faqs,
        ]);
    }

    #[Route('/new', name: 'app_faq_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $faq = new Faq();
        $faq->setIsActive(true);

        $form = $this->createForm(FaqType::class, $faq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($faq);
            $em->flush();
            $this->addFlash('success', 'FAQ créée avec succès !');
            return $this->redirectToRoute('app_faq_index');
        }

        return $this->render('faq_admin/form.html.twig', [
            'faq' => $faq,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_faq_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Faq $faq, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(FaqType::class, $faq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $faq->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('success', 'FAQ mise à jour !');
            return $this->redirectToRoute('app_faq_index');
        }

        return $this->render('faq_admin/form.html.twig', [
            'faq' => $faq,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_faq_delete', methods: ['POST'])]
    public function delete(Request $request, Faq $faq, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$faq->getId(), $request->request->get('_token'))) {
            $em->remove($faq);
            $em->flush();
            $this->addFlash('success', 'FAQ supprimée.');
        }
        return $this->redirectToRoute('app_faq_index');
    }
}


