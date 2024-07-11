<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SearchController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/search', name: 'app_search')]
    public function index(): Response
    {
        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/search/keyword', name: 'app_search_action')]
    public function searchWithKeyword(){
        $request = Request::createFromGlobals();
        $keyword_1 = $request->query->get('keyword_1');

        return $this->render('search/search_result.html.twig', [
            'keyword_1' => $keyword_1
        ]);
    }
}
