<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Parcours/ParcoursV2Controller.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/01/2026 22:28
 */

namespace App\Controller\Formation;

use App\Classes\GetDpeParcours;
use App\Controller\BaseController;
use App\Entity\Annee;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\SemestreParcours;
use App\Form\FormationStep1Type;
use App\Form\FormationStep2Type;
use App\Form\FormationStep3Type;
use App\Form\ParcoursStep1Type;
use App\Form\ParcoursStep2Type;
use App\Form\ParcoursStep5Type;
use App\Form\ParcoursStep6Type;
use App\Form\ParcoursStep7Type;
use App\Form\Type\TextareaAutoSaveType;
use App\Repository\FormationTabStateRepository;
use App\Repository\ParcoursTabStateRepository;
use App\Repository\ValidationIssueRepository;
use App\Service\LheoXML;
use App\Service\Validation\SemesterValidationRefresher;
use App\Service\VersioningFormation;
use App\Service\VersioningParcours;
use App\TypeDiplome\TypeDiplomeResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/formation/v2', name: 'formation_v2_')]
#[IsGranted('ROLE_ADMIN')]
class FormationController extends BaseController
{
    #[Route('/{slug}/modifier', name: 'modifier')]
    public function modifier(
        Request                     $request,
        ParcoursTabStateRepository $parcoursTabStateRepository,
        FormationTabStateRepository $statesRepo,
        TypeDiplomeResolver         $typeDiplomeResolver,
        Formation                   $formation
    ): Response
    {
        $tabStates = $statesRepo->indexByTabKey($formation);

        $parameters = [
            'tab' => 'localisation',
            'formation' => $formation,
            'typeDiplome' => $formation->getTypeDiplome(),
            'form' => $this->createForm(FormationStep1Type::class, $formation),
            'titre' => 'Localisation et organisation de la formation',
            'texte_help' => 'Indiquez les éléments de localisation et d\'organisation de la formation',
            'tabStates' => $tabStates,
        ];

        if ($formation->hasParcours() === false) {
            $parcours = $formation->getParcours()->first();
            //pas de parcours, donc on calcul les data du parcours par défaut
            $tabStatesParcours = $parcoursTabStateRepository->indexByTabKey($parcours);
            $typeD = $typeDiplomeResolver->fromParcours($parcours);
            $dto = $typeD->calculStructureParcours($parcours);

            $parameters['tabStatesParcours'] = $tabStatesParcours;
            $parameters['dto'] = $dto;
        }

        // Si Turbo charge un frame, ne calcule pas la structure complète
        if ($request->headers->has('Turbo-Frame')) {
            return $this->render('parcours_v2/tabs/_presentation.html.twig', [
                'formation' => $formation,
                'form' => $parameters['form']->createView(),
                'titre' => $parameters['titre'],
                'texte_help' => $parameters['texte_help'],
                'tabStates' => $tabStates,
            ]);
        }

        return $this->render('formation_v2/modifier.html.twig', array_merge($parameters, [
        ]));
    }

    #[Route('/{slug}', name: 'voir', methods: ['GET'])]
    public function show(
        Formation $formation,
        LheoXML   $lheoXML,
    ): Response
    {
        $typeDiplome = $formation->getTypeDiplome();
        if ($typeDiplome === null) {
            throw $this->createNotFoundException();
        }

        $typeD = $this->typeDiplomeResolver->fromTypeDiplome($typeDiplome);

//        $textDifferencesParcours = $versioningParcours->getDifferencesBetweenParcoursAndLastVersion($parcours);
//        $textDifferencesFormation = $versioningFormation->getDifferencesBetweenFormationAndLastVersion($formation);
//        $version = $versioningParcours->hasLastVersion($parcours);
//
//        $cssDiff = DiffHelper::getStyleSheet();


//        // Ordre des semestres manquants
//        $missingSemestre = [];
//
//        // Si le parcours est en alternance sans les premiers semestres
//        // on met un lien vers le parcours de base
//        $parcoursDeBase = null;
//        if($parcours->getTypeParcours() === TypeParcoursEnum::TYPE_PARCOURS_ALTERNANCE
//            && $parcours->getFormation()?->getTypeDiplome()?->getLibelleCourt() === 'BUT'
//        ) {
//            $parcoursDeBase = $entityManager->getRepository(Parcours::class)
//                ->findParcoursDeBaseAlternance(
//                    $parcours->getLibelle(),
//                    GetDpeParcours::getFromParcours($parcours)?->getCampagneCollecte()?->getId()
//                );
//            $parcoursDeBase = count($parcoursDeBase) > 0 ? $parcoursDeBase[0] : null;
//
//            $missingSemestre = $entityManager->getRepository(Parcours::class)
//                ->findParcoursAlternanceHasMissingSemestre($parcours);
//        }
        return $this->render('formation_v2/voir.html.twig', [
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'hasParcours' => $formation->isHasParcours(),
            'typeD' => $typeD,
            'lheoXML' => $lheoXML,
        ]);
    }

    #[Route('/{parcours}/modifier/annee/{annee}', name: 'annee')]
    public function annee(Parcours $parcours, Annee $annee): Response
    {
        return $this->render('parcours_v2/tabs/_annee.html.twig', [
            'annee' => $annee,
            'parcours' => $parcours
        ]);
    }

    #[Route('/{parcours}/modifier/semestre/{semestreParcours}', name: 'semestre')]
    public function semestre(
        ValidationIssueRepository   $validationIssueRepository,
        Request                     $request,
        TypeDiplomeResolver         $typeDiplomeResolver,
        Parcours                    $parcours,
        SemestreParcours            $semestreParcours,
        SemesterValidationRefresher $refresher
    ): Response
    {
        // refresh du semestre si dirty
        $refresher->refreshIfDirty($semestreParcours, $parcours);

        $typeD = $typeDiplomeResolver->fromParcours($parcours);
        $dtoSemestre = $typeD->calculStructureSemestre($semestreParcours, $parcours);

        $parameters = [
            'semestreParcours' => $semestreParcours,
            'semestre' => $dtoSemestre,
            'parcours' => $parcours,
            'validationsIssues' => $validationIssueRepository->findBySemestre($dtoSemestre->semestre->getId())
        ];

        // Si la requête vient d'un Turbo Frame (header `Turbo-Frame` présent), renvoyer uniquement le fragment
        if ($request->headers->has('Turbo-Frame')) {
            return $this->render('parcours_v2/tabs/_semestre.html.twig', $parameters);
        }

        $dto = $typeD->calculStructureParcours($parcours);

        // Sinon renvoyer la page complète (index) qui inclura le fragment dans son corps
        return $this->render('parcours_v2/modifier.html.twig', array_merge($parameters, [
            'tab' => 'semestre',
            'dpeParcours' => GetDpeParcours::getFromParcours($parcours),
            'dto' => $dto
        ]));
    }

    #[Route('/{parcours}/modifier/semestre/{semestreParcours}/validation', name: 'semestre_validation')]
    public function semestreValidation(
        Request                     $request,
        TypeDIplomeResolver         $typeDiplomeResolver,
        Parcours                    $parcours,
        SemestreParcours            $semestreParcours,
        SemesterValidationRefresher $refresher
    ): Response
    {
        // refresh du semestre si dirty
        $refresher->forceRefresh($semestreParcours, $parcours);

        $typeD = $typeDiplomeResolver->fromParcours($parcours);
        $dtoSemestre = $typeD->calculStructureSemestre($semestreParcours, $parcours);

        $parameters = [
            'semestreParcours' => $semestreParcours,
            'semestre' => $dtoSemestre,
            'parcours' => $parcours
        ];

        return $this->render('parcours_v2/tabs/_semestre.html.twig', $parameters);
    }

    #[Route('/{formation}/modifier/tabs/{tab}', name: 'tabs')]
    public function tabs(
        TypeDiplomeResolver         $typeDiplomeResolver,
        FormationTabStateRepository $statesRepo,
        Formation                   $formation, string $tab, Request $request): Response
    {
        $form = null;
        $tabView = $tab;
        $titre = null;
        $texte_help = null;

        switch ($tab) {
            case 'localisation':
                $form = $this->createForm(FormationStep1Type::class, $formation);
                $titre = 'Localisation et organisation de la formation';
                $texte_help = 'Indiquez les éléments de localisation et d\'organisation de la formation';
                break;
            case 'presentation':
                $form = $this->createForm(FormationStep2Type::class, $formation);
                $titre = 'Présentation de la formation';
                $texte_help = 'Indiquez les éléments descriptifs de la formation';
                break;
            case 'structure':
                $titre = 'Structure de la formation';
                $texte_help = 'Indiquez les éléments structurant de la formation';
                $form = $this->createForm(FormationStep3Type::class, $formation);
                break;

        }

        $tabStates = $statesRepo->indexByTabKey($formation);

        $parameters = [
            'formation' => $formation,
            'form' => $form?->createView(),
            'titre' => $titre,
            'texte_help' => $texte_help,
            'tabStates' => $tabStates,
            'typeDiplome' => $formation->getTypeDiplome(),
        ];

        // Si la requête vient d'un Turbo Frame (header `Turbo-Frame` présent), renvoyer uniquement le fragment
        if ($request->headers->has('Turbo-Frame')) {
            return $this->render('formation_v2/tabs/_' . $tabView . '.html.twig', $parameters);
        }

        $typeD = $typeDiplomeResolver->fromFormation($formation);

        // Sinon renvoyer la page complète (index) qui inclura le fragment dans son corps
        return $this->render('formation_v2/modifier.html.twig', array_merge($parameters, [
            'tab' => $tabView
        ]));
    }
}
