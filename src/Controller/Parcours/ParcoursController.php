<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Parcours/ParcoursV2Controller.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/01/2026 22:28
 */

namespace App\Controller\Parcours;

use App\Classes\GetDpeParcours;
use App\Classes\GetElementConstitutif;
use App\Controller\BaseController;
use App\Entity\Annee;
use App\Entity\ElementConstitutif;
use App\Entity\Parcours;
use App\Entity\SemestreParcours;
use App\Form\EcStep4Type;
use App\Form\ParcoursStep1Type;
use App\Form\ParcoursStep2Type;
use App\Form\ParcoursStep5Type;
use App\Form\ParcoursStep6Type;
use App\Form\ParcoursStep7Type;
use App\Repository\ParcoursRepository;
use App\TypeDiplome\TypeDiplomeResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/parcours/v2')]
#[IsGranted('ROLE_ADMIN')]
class ParcoursController extends BaseController
{
    #[Route('/{parcours}', name: 'app_parcours_v2')]
    public function index(TypeDIplomeResolver $typeDiplomeResolver, Parcours $parcours): Response
    {
        $typeD = $typeDiplomeResolver->fromParcours($parcours);
        $dto = $typeD->calculStructureParcours($parcours);

        return $this->render('parcours_v2/index.html.twig', [
            'tab' => 'maquette',
            'parcours' => $parcours,
            'dpeParcours' => GetDpeParcours::getFromParcours($parcours),
            'dto' => $dto
        ]);
    }

    #[Route('/{parcours}/annee/{annee}', name: 'app_parcours_v2_annee')]
    public function annee(Parcours $parcours, Annee $annee): Response
    {
        return $this->render('parcours_v2/tabs/_annee.html.twig', [
            'annee' => $annee,
            'parcours' => $parcours
        ]);
    }

    #[Route('/{parcours}/semestre/{semestreParcours}', name: 'app_parcours_v2_semestre')]
    public function semestre(
        Request $request,
        TypeDIplomeResolver $typeDiplomeResolver,
        Parcours            $parcours,
        SemestreParcours    $semestreParcours
    ): Response
    {
        //todo: avoir un DTO générique qui pioche les bonnes informations selon le type de diplôme. Peut être que _semestre devrait être dépendant du type de diplome
        $typeD = $typeDiplomeResolver->fromParcours($parcours);
        $dtoSemestre = $typeD->calculStructureSemestre($semestreParcours, $parcours);

//        return $this->render('parcours_v2/tabs/_semestre.html.twig', [
//            'semestreParcours' => $semestreParcours,
//            'semestre' => $dtoSemestre,
//            'parcours' => $parcours
//        ]);

        $parameters = [
            'semestreParcours' => $semestreParcours,
            'semestre' => $dtoSemestre,
            'parcours' => $parcours
        ];

        // Si la requête vient d'un Turbo Frame (header `Turbo-Frame` présent), renvoyer uniquement le fragment
        if ($request->headers->has('Turbo-Frame')) {
            return $this->render('parcours_v2/tabs/_semestre.html.twig', $parameters);
        }

        $typeD = $typeDiplomeResolver->fromParcours($parcours);
        $dto = $typeD->calculStructureParcours($parcours);

        // Sinon renvoyer la page complète (index) qui inclura le fragment dans son corps
        return $this->render('parcours_v2/index.html.twig', array_merge($parameters, [
            'tab' => 'semestre',
            'dpeParcours' => GetDpeParcours::getFromParcours($parcours),
            'dto' => $dto
        ]));
    }

    #[Route('/{parcours}/tabs/{tab}', name: 'app_parcours_v2_tabs')]
    public function tabs(Parcours $parcours, string $tab, Request $request): Response
    {
        $form = null;
        switch ($tab) {
            case 'presentation':
                $form = $this->createForm(ParcoursStep1Type::class, $parcours);
                $tabView = 'presentation';
                $titre = 'Présentation du parcours';
                $texte_help = 'Indiquez les éléments du parcours';
                break;
            case 'descriptif':
                $form = $this->createForm(ParcoursStep2Type::class, $parcours);
                $tabView = 'tabs';
                $titre = 'Descriptif du parcours';
                $texte_help = 'Description du parcours';
                break;
            case 'admission':
                $form = $this->createForm(ParcoursStep5Type::class, $parcours);
                $tabView = 'tabs';
                $titre = 'Admission au parcours';
                $texte_help = '...';
                break;
            case 'et_apres':
                $form = $this->createForm(ParcoursStep6Type::class, $parcours);
                $tabView = 'tabs';
                $titre = 'Et après le parcours';
                $texte_help = '...';
                break;
            case 'configuration':
                $form = $this->createForm(ParcoursStep7Type::class, $parcours);
                $tabView = 'tabs';
                $titre = 'Configuration du parcours';
                $texte_help = '...';
                break;
            default:
                $tabView = $tab;
        }

        $parameters = [
            'parcours' => $parcours,
            'form' => $form?->createView(),
            'titre' => $titre,
            'texte_help' => $texte_help
        ];

        // Si la requête vient d'un Turbo Frame (header `Turbo-Frame` présent), renvoyer uniquement le fragment
        if ($request->headers->has('Turbo-Frame')) {
            return $this->render('parcours_v2/tabs/_' . $tabView . '.html.twig', $parameters);
        }

        // Sinon renvoyer la page complète (index) qui inclura le fragment dans son corps
        return $this->render('parcours_v2/index.html.twig', array_merge($parameters, [
            'tab' => $tabView,
        ]));
    }
}
