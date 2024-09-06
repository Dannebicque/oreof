<?php

namespace App\Controller;

use App\Entity\FicheMatiere;
use App\Entity\Formation;
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
    #[Route('/recherche/mot_cle', name: 'app_search_action')]
    public function searchWithKeyword(
        EntityManagerInterface $entityManager,
    ){
        $request = Request::createFromGlobals();
        $keyword_1 = $request->query->get('keyword_1');
        $typeRecherche = $request->query->get('searchType');

        $typeRechercheValide = $typeRecherche;
        if(in_array($typeRecherche, ['parcours', 'ficheMatiere']) === false){
            $typeRechercheValide = 'parcours';
        }

        if(!isset($keyword_1) || empty($keyword_1)|| mb_strlen($keyword_1) <= 2){
            $this->addFlash('toast', [
                'type' => 'error',
                'text' => 'Le mot-clé fourni doit faire au moins 3 caractères.'
            ]);

            return $this->redirectToRoute('app_search');
        }

        if($typeRechercheValide === 'parcours'){

            $resultArrayBadge = [];
            $isParcoursParDefautArray = [];

            $parcoursArray = $entityManager->getRepository(Parcours::class)->findWithKeyword($keyword_1);
            $parcoursParDefautArray = $entityManager->getRepository(Parcours::class)->findWithKeywordForDefaultParcours($keyword_1);

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

                $parcours = $entityManager
                    ->getRepository(Parcours::class)
                    ->findOneById($parcoursArray[$i]['parcours_id']);

                $linkedFicheMatiere = $entityManager
                    ->getRepository(FicheMatiere::class)
                    ->findForParcoursWithKeyword($parcours, $keyword_1);

                $libelleMention = $entityManager
                    ->getRepository(Formation::class)
                    ->findOneById($parcoursArray[$i]['formation_id'])
                    ->getDisplayLong() ?? "";

                $resultArrayBadge[] = 
                [
                    ...$textContains, 
                    'fichesMatieres' => [...$linkedFicheMatiere],
                    'libelleMention' => $libelleMention 
                ];

                $isParcoursParDefautArray[] = $parcoursArray[$i]['parcours_libelle'] === Parcours::PARCOURS_DEFAUT;
            }

            for($j = 0; $j < count($parcoursParDefautArray); $j++){
                $textContainsDefault = [];
                if($this->isStringContainingText($keyword_1, $parcoursParDefautArray[$j]['contenuFormation'])){
                    $textContainsDefault[] = 'contenuFormation';
                }
                if($this->isStringContainingText($keyword_1, $parcoursParDefautArray[$j]['resultatsAttendus'])){
                    $textContainsDefault[] = 'resultatsAttendus';
                }
                if($this->isStringContainingText($keyword_1, $parcoursParDefautArray[$j]['objectifsFormation'])){
                    $textContainsDefault[] = 'objectifsFormation';
                }
                if($this->isStringContainingText($keyword_1, $parcoursParDefautArray[$j]['poursuitesEtudes'])){
                    $textContainsDefault[] = 'poursuitesEtudes';
                }

                $parcoursDefaut = $entityManager
                    ->getRepository(Parcours::class)
                    ->findOneById($parcoursParDefautArray[$j]['parcours_id']);

                $linkedFicheMatiereDefault = $entityManager
                    ->getRepository(FicheMatiere::class)
                    ->findForParcoursWithKeyword($parcoursDefaut, $keyword_1);

                $libelleMentionParDefaut = $entityManager
                    ->getRepository(Formation::class)
                    ->findOneById($parcoursParDefautArray[$j]['formation_id'])
                    ->getDisplayLong() ?? "";

                $resultArrayBadge[] = [
                    ...$textContainsDefault,
                    'fichesMatieres' => [...$linkedFicheMatiereDefault],
                    'libelleMention' => $libelleMentionParDefaut
                ];

                $isParcoursParDefautArray[] = $parcoursParDefautArray[$j]['parcours_libelle'] === Parcours::PARCOURS_DEFAUT;
            }

            $dataTwigRenderer = [
                'typeRecherche' => 'parcours',
                'parcoursArray' => [
                ...$parcoursArray,
                ...$parcoursParDefautArray
                ],
                'resultArrayBadge' => $resultArrayBadge,
                'isParcoursDefautArray' => $isParcoursParDefautArray 
            ];
        }
        elseif($typeRechercheValide === 'ficheMatiere'){
            $countFiche = $entityManager
                ->getRepository(FicheMatiere::class)
                ->findCountForKeyword($keyword_1)[0]['nombre_total'];

            $dataTwigRenderer = [
                'typeRecherche' => 'ficheMatiere',
                'nombreTotal' => $countFiche
            ];
        }

        return $this->render('search/search_result.html.twig', [
            'keyword_1' => $keyword_1,
            ...$dataTwigRenderer
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
