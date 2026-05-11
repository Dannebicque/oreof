<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Entity\User;
use App\Repository\FaqRepository;
use App\Service\HelpGrantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FaqController extends AbstractController
{
    #[Route('/faq', name: 'app_faq')]
    public function index(FaqRepository $faqRepository, HelpGrantService $helpGrantService): Response
    {
        $faqs = $faqRepository->findActiveOrdered();
        $user = $this->getUser();
        
        // Filtre les FAQs selon les droits d'accès par centre
        $faqs = array_values(array_filter($faqs, fn (Faq $faq) => $helpGrantService->isAllowed($faq, $user instanceof User ? $user : null)));

        return $this->render('faq/index.html.twig', [
            'faqs' => $faqs,
        ]);
    }
}
