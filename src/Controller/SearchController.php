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
use App\Utils\Tools;


class SearchController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/recherche/parcours', name: 'app_search')]
    public function index(): Response
    {
        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/recherche/parcours/mot_cle', name: 'app_search_action')]
    public function searchWithKeyword(
        EntityManagerInterface $entityManager
    ){
        $request = Request::createFromGlobals();
        $keyword_1 = $request->query->get('keyword_1');

        if(!isset($keyword_1) || empty($keyword_1)|| mb_strlen($keyword_1) <= 2){
            $this->addFlash('toast', [
                'type' => 'error',
                'text' => 'Le mot-clé fourni doit faire au moins 3 caractères.'
            ]);

            return $this->redirectToRoute('app_search');
        }

        $resultArrayBadge = [];
        $parcoursArray = $entityManager->getRepository(Parcours::class)->findWithKeyword($keyword_1);

        for($i = 0; $i < count($parcoursArray); $i++){
            $textContains = [];
            if( $this->isStringContainingText($keyword_1, $parcoursArray[$i]['contenuFormation'])) {
                $textContains[] = 'contenuFormation';
            }
            if( $this->isStringContainingText($keyword_1, $parcoursArray[$i]['poursuitesEtudes'])) {
                $textContains[] = 'poursuitesEtudes';
            }
            if( $this->isStringContainingText($keyword_1, $parcoursArray[$i]['objectifsParcours'])) {
                $textContains[] = 'objectifsParcours';
            }
            if( $this->isStringContainingText($keyword_1, $parcoursArray[$i]['resultatsAttendus'])) {
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

    private function isStringContainingText(string $needle, string|null $haystack) : bool {
        if($haystack !== null){
            return
                mb_strstr(
                    mb_strtoupper(Tools::removeAccent($haystack)),
                    mb_strtoupper(Tools::removeAccent($needle))
                );
        }
        else {
            return false;
        }
    }
}
