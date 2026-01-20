<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Parcours/ParcoursV2Controller.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/01/2026 22:28
 */

namespace App\Controller\Parcours;

use App\Controller\BaseController;
use App\Entity\Parcours;
use App\Entity\SemestreParcours;
use App\Entity\TypeUe;
use App\Entity\Ue;
use App\Form\UeType;
use App\Repository\TypeUeRepository;
use App\Repository\UeRepository;
use App\TypeDiplome\TypeDiplomeResolver;
use App\Utils\JsonRequest;
use App\Utils\TurboStreamResponseFactory;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/parcours/v2/structure/ue/', name: 'parcours_structure')]
#[IsGranted('ROLE_ADMIN')]
class ParcoursUeController extends BaseController
{
    #[Route('/{parcours}/semestre/{semestreParcours}/ajout-ue', name: '_add_ue_semestre')]
    public function addUe(
        TurboStreamResponseFactory $turboStream,
        TypeUeRepository           $typeUeRepository,
        Request                    $request,
        UeRepository               $ueRepository,
        SemestreParcours           $semestreParcours,
        Parcours                   $parcours,
        TypeDiplomeResolver        $typeDiplomeResolver
    ): Response
    {
        $ue = new Ue();
        $semestre = $semestreParcours->getSemestre();
        $ue->setSemestre($semestre);
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        //trouver l'ordre max de l'UE dans le semestre pour ajouter à la fin


        if ($typeDiplome === null) {
            throw new Exception('Type de diplôme non trouvé');
        }

        $form = $this->createForm(UeType::class, $ue, [
            'action' => $this->generateUrl('parcours_structure_add_ue_semestre', [
                'semestreParcours' => $semestreParcours->getId(),
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


            // recalculer le DTO du semestre pour renvoyer le fragment mis à jour
            $typeD = $typeDiplomeResolver->fromParcours($parcours);
            $dtoSemestre = $typeD->calculStructureSemestre($semestreParcours, $parcours);

            // Message de toast
            $toastMessage = 'UE créée avec succès';

            $html = $this->renderView('parcours_v2/turbo/add_ue_success.stream.html.twig', [
                'parcours' => $parcours,
                'semestreParcours' => $semestreParcours,
                'semestre' => $dtoSemestre,
                'toastMessage' => $toastMessage,
                'newUeId' => $ue->getId(),
            ]);

            return $turboStream->stream($html);
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
                'subtitle' => 'Dans : semestre ' . $semestreParcours->getSemestre()?->getOrdre(),
                'body' => $body,
                'footer' => $footer,
            ]),
            200,
            ['Content-Type' => 'text/vnd.turbo-stream.html']
        );

//        return new Response(
//            $this->renderView('_ui/open.stream.html.twig', [
//                'title' => 'Ajouter une UE',
//                'subtitle' => 'Dans : semestre ' . $semestre->getOrdre(),
//                'body' => $body,
//                'footer' => $footer,
//            ]),
//            200,
//            ['Content-Type' => 'text/vnd.turbo-stream.html']
//        );
    }


    #[Route('/{parcours}/semestre/{semestreParcours}/{ue}/pre-delete-ue', name: '_pre_delete_ue_semestre')]
    public function preDeleteUe(
        TurboStreamResponseFactory $turboStream,
        CsrfTokenManagerInterface  $csrfTokenManager,
        TypeUeRepository           $typeUeRepository,
        Request                    $request,
        UeRepository               $ueRepository,
        SemestreParcours           $semestreParcours,
        Parcours                   $parcours,
        TypeDiplomeResolver        $typeDiplomeResolver,
        Ue                         $ue
    ): Response
    {
        $body = $this->renderView('parcours/v2/ue/_delete.html.twig', [
        ]);

        $footer = $this->renderView('_ui/_footer_delete_cancel.html.twig', [
            'submitLabel' => 'Supprimer l\'UE',
            'url' => $this->generateUrl('parcours_structure_delete_ue_semestre', [
                'semestreParcours' => $semestreParcours->getId(),
                'parcours' => $parcours->getId(),
                'ue' => $ue->getId()
            ]),
            'csrf_token' => $csrfTokenManager->getToken('delete' . $ue->getId()),
        ]);

        return new Response(
            $this->renderView('_ui/open.stream.html.twig', [
                'title' => 'Supprimer une UE',
                'subtitle' => 'Dans : semestre ' . $semestreParcours->getSemestre()?->getOrdre(),
                'body' => $body,
                'footer' => $footer,
            ]),
            200,
            ['Content-Type' => 'text/vnd.turbo-stream.html']
        );

//        return $turboStream->appendStreams(
//            ...[
//                $body, $footer
//            ]
//            $this->renderView('_ui/open.stream.html.twig', [
//                'title' => 'Supprimer une UE',
//                'subtitle' => 'Dans : semestre ' . $semestreParcours->getSemestre()?->getOrdre(),
//                'body' => $body,
//                'footer' => $footer,
//            ])
//        );
    }

    #[Route('/{parcours}/semestre/{semestreParcours}/{ue}/delete-ue', name: '_delete_ue_semestre')]
    public function deleteUe(
        TurboStreamResponseFactory $turboStream,
        Request                    $request,
        SemestreParcours           $semestreParcours,
        Parcours                   $parcours,
        Ue                         $ue,
        TypeDiplomeResolver        $typeDiplomeResolver
    ): Response
    {
        if ($this->isCsrfTokenValid(
            'delete' . $ue->getId(), $request->query->get('csrf_token')
        )) {
            $typeD = $typeDiplomeResolver->fromParcours($parcours);
            $dtoSemestre = $typeD->calculStructureSemestre($semestreParcours, $parcours);

            //todo: supprimer l'UE, les UE enfants, les EC associés, renuméroter
            //faire un service avec renumérotation en option, pourra servir pour la suppression d'un semestre ou d'un aprcours

            // Message de toast
            $toastMessage = 'UE supprimée avec succès';

            $html = $this->renderView('parcours_v2/turbo/add_ue_success.stream.html.twig', [
                'parcours' => $parcours,
                'semestreParcours' => $semestreParcours,
                'semestre' => $dtoSemestre,
                'toastMessage' => $toastMessage,
                'newUeId' => $ue->getId(),
            ]);

            return $turboStream->stream($html);
        }

        return $turboStream->streamToastError(
            'Erreur lors de la suppression de l\'UE',
        );
    }
}
