<?php

namespace App\Controller;

use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use Doctrine\ORM\EntityManagerInterface;
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
    public function searchWithKeyword(
        EntityManagerInterface $entityManager
    ){
        $request = Request::createFromGlobals();
        $keyword_1 = $request->query->get('keyword_1');

        $resultArrayBadge = [];
        $parcoursArray = $entityManager->getRepository(Parcours::class)->findWithKeyword($keyword_1);

        for($i = 0; $i < count($parcoursArray); $i++){
            $textContains = [];
            if(
                mb_strstr(
                    mb_strtoupper($parcoursArray[$i]['contenuFormation']),
                    mb_strtoupper($keyword_1))
                !== false
            ){
                $textContains[] = 'contenuFormation';
            }
            if(
                mb_strstr(
                    mb_strtoupper($parcoursArray[$i]['poursuitesEtudes']),
                    mb_strtoupper($keyword_1))
                !== false
            ){
                $textContains[] = 'poursuitesEtudes';
            }
            if(
                mb_strstr(
                    mb_strtoupper($parcoursArray[$i]['objectifsParcours']),
                    mb_strtoupper($keyword_1))
                !== false
            ){
                $textContains[] = 'objectifsParcours';
            }
            if(
                mb_strstr(
                    mb_strtoupper($parcoursArray[$i]['resultatsAttendus']),
                    mb_strtoupper($keyword_1))
                !== false
            ){
                $textContains[] = 'resultatsAttendus';
            }

            $parcours = $entityManager->getRepository(Parcours::class)->findOneById($parcoursArray[$i]['id']);
            $linkedFicheMatiere = $entityManager->getRepository(FicheMatiere::class)->findForParcoursWithKeyword($parcours, $keyword_1);

            $resultArrayBadge[] = 
            [
                ...$textContains, 
                'fichesMatieres' => [...$linkedFicheMatiere]
            ];
        }

        return $this->render('search/search_result.html.twig', [
            'keyword_1' => $keyword_1,
            'parcoursArray' => $parcoursArray,
            'resultArrayBadge' => $resultArrayBadge
        ]);
    }
}
