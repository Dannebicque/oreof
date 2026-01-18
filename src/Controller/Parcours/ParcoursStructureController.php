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
use App\Classes\UeOrdre;
use App\Controller\BaseController;
use App\Entity\Annee;
use App\Entity\ElementConstitutif;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\SemestreParcours;
use App\Entity\TypeUe;
use App\Entity\Ue;
use App\Form\EcStep4Type;
use App\Form\ParcoursStep1Type;
use App\Form\ParcoursStep2Type;
use App\Form\ParcoursStep5Type;
use App\Form\ParcoursStep6Type;
use App\Form\ParcoursStep7Type;
use App\Form\UeType;
use App\Repository\NatureUeEcRepository;
use App\Repository\ParcoursRepository;
use App\Repository\TypeUeRepository;
use App\Repository\UeRepository;
use App\TypeDiplome\TypeDiplomeResolver;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/parcours/v2/structure/', name: 'parcours_structure')]
#[IsGranted('ROLE_ADMIN')]
class ParcoursStructureController extends BaseController
{
    #[Route('/{parcours}/ec/{id}', name: '_saisir_heures', methods: ['GET'])]
    public function ec(Parcours $parcours, ElementConstitutif $elementConstitutif): Response
    {
        $isParcoursProprietaire = $elementConstitutif->getFicheMatiere()?->getParcours()?->getId() === $parcours->getId();

        $getElement = new GetElementConstitutif($elementConstitutif, $parcours);
        $ecHeures = $getElement->getFicheMatiereHeures();

        $form = $this->createForm(EcStep4Type::class, $ecHeures, [
            'isModal' => true,
            'data_class' => $ecHeures::class,
            'modalite' => $parcours->getModalitesEnseignement(),
            'action' => $this->generateUrl(
                'app_element_constitutif_structure',
                [
                    'id' => $elementConstitutif->getId(),
                    'parcours' => $parcours->getId()
                ]
            ),
        ]);

        return $this->render('parcours_v2/ec/_heures_ec.html.twig', [
            'ec' => $elementConstitutif,
            'parcours' => $parcours,
            'form' => $form->createView(),
            'isParcoursProprietaire' => $isParcoursProprietaire,
            'modalite' => $parcours->getModalitesEnseignement()
        ]);
    }

    #[Route('/{parcours}/ec/{id}', name: '_saisir_heures_post', methods: ['POST'])]
    public function ecPost(
        Request            $request,
        Parcours           $parcours,
        ElementConstitutif $id
    ): Response
    {
        //        $user = new User();
        //        $form = $this->createForm(UserType::class, $user, [
        //            'action' => $this->generateUrl('user_create_modal'),
        //            'method' => 'POST',
        //        ]);
        //        $form->handleRequest($request);
        //
        //        if (!$form->isSubmitted() || !$form->isValid()) {
        // 422 conseillé avec Turbo : Turbo garde le contexte et affiche les erreurs
        //            return $this->render('user/modal_form.html.twig', [
        //                'form' => $form,
        //                'title' => 'Créer un utilisateur',
        //            ], new Response(status: 422));
        //        }

        //        $em->persist($user);
        //        $em->flush();

        // Turbo Stream si Turbo le demande (Accept: text/vnd.turbo-stream.html)
        $accept = $request->headers->get('Accept', '');
        $wantsTurboStream = str_contains($accept, 'text/vnd.turbo-stream.html');

        if ($wantsTurboStream) {
            return $this->render('parcours_v2/ec/_heures_ec_valide.html.twig', [
                'ec' => $id,
                'parcours' => $parcours,
            ], new Response(headers: ['Content-Type' => 'text/vnd.turbo-stream.html']));
        }
    }

    #[Route('/{parcours}/semestre/{semestre}/ajout-ue', name: '_add_ue_semestre')]
    public function addUe(
        TypeUeRepository $typeUeRepository,
        Request          $request,
        UeRepository     $ueRepository,
        Semestre         $semestre,
        Parcours         $parcours
    ): Response
    {
        $ue = new Ue();
        $ue->setSemestre($semestre);
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        //trouver l'ordre max de l'UE dans le semestre pour ajouter à la fin


        if ($typeDiplome === null) {
            throw new Exception('Type de diplôme non trouvé');
        }

        $form = $this->createForm(UeType::class, $ue, [
            'action' => $this->generateUrl('structure_ue_add_ue_semestre', [
                'semestre' => $semestre->getId(),
                'parcours' => $parcours->getId(),
            ]),
            'attr' => ['id' => 'modal_form'],
            'typeDiplome' => $typeDiplome,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ordre = $ueRepository->getMaxOrdre($semestre);
            $ue->setOrdre($ordre + 1);


            if ($form->get('typeUeTexte')->getData() !== null && $form->get('typeUe')->getData() === null) {
                $tu = new TypeUe();
                $tu->setLibelle($form->get('typeUeTexte')->getData());
                $tu->addTypeDiplome($typeDiplome);
                $typeUeRepository->save($tu, true);
                $ue->setTypeUe($tu);
            }
            $ueRepository->save($ue, true);

            if ($ue->getNatureUeEc()?->isChoix() === true && $ue->getUeParent() === null) {
                if ($ue->getUeEnfants()->count() === 0) {
                    //on ajoute par défaut deux UE enfants
                    $ueEnfant1 = new Ue();
                    $ueEnfant1->setSemestre($semestre);
                    $ueEnfant1->setNatureUeEc(null);
                    $ueEnfant1->setNatureUeEc($ue->getNatureUeEc());
                    $ueEnfant1->setOrdre(1);
                    $ueEnfant1->setUeParent($ue);
                    $ueEnfant1->setLibelle('Choix 1');
                    $ueRepository->save($ueEnfant1, true);

                    $ueEnfant2 = new Ue();
                    $ueEnfant2->setSemestre($semestre);
                    $ueEnfant2->setNatureUeEc(null);
                    $ueEnfant2->setNatureUeEc($ue->getNatureUeEc());
                    $ueEnfant2->setOrdre(2);
                    $ueEnfant2->setUeParent($ue);
                    $ueEnfant2->setLibelle('Choix 2');
                    $ueRepository->save($ueEnfant2, true);
                }
            }


            return $this->json(true);
        }

        $body = $this->renderView('parcours/v2/ue/_new.html.twig', [
            'form' => $form->createView(),
        ]);

        $footer = $this->renderView('_ui/_footer_submit_cancel.html.twig', [
            'submitLabel' => 'Créer l\'UE',
        ]);

        return new Response(
            $this->renderView('_ui/open.stream.html.twig', [
                'title' => 'Ajouter une UE',
                'subtitle' => 'Dans : semestre ' . $semestre->getOrdre(),
                'body' => $body,
                'footer' => $footer,
            ]),
            200,
            ['Content-Type' => 'text/vnd.turbo-stream.html']
        );
    }
}
