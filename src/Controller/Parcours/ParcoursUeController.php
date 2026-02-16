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
use App\Entity\NatureUeEc;
use App\Entity\Parcours;
use App\Entity\SemestreParcours;
use App\Entity\Ue;
use App\Form\UeType;
use App\Repository\NatureUeEcRepository;
use App\Repository\UeRepository;
use App\TypeDiplome\TypeDiplomeResolver;
use App\Utils\TurboStreamResponseFactory;
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
        Request                    $request,
        UeRepository               $ueRepository,
        NatureUeEcRepository $natureUeEcRepository,
        SemestreParcours           $semestreParcours,
        Parcours                   $parcours,
        TypeDiplomeResolver        $typeDiplomeResolver
    ): Response
    {
        $ue = new Ue();
        $semestre = $semestreParcours->getSemestre();

        if ($semestre === null) {
            throw new \RuntimeException('La semestre n\'existe pas');
        }

        $ue->setSemestre($semestre);
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        if ($typeDiplome === null) {
            throw new \RuntimeException('Type de diplôme non trouvé');
        }

        $form = $this->createForm(UeType::class, $ue, [
            'action' => $this->generateUrl('parcours_structure_add_ue_semestre', [
                'semestreParcours' => $semestreParcours->getId(),
                'parcours' => $parcours->getId(),
            ]),
            'typeDiplome' => $typeDiplome,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ordre = $ueRepository->getMaxOrdre($semestre);
            $ue->setOrdre($ordre + 1);
            $ueRepository->save($ue, true);
            $ueObligatoire = $natureUeEcRepository->findOneBy(['type' => NatureUeEc::Nature_UE, 'choix' => false, 'libre' => false]);

            if ($ue->getNatureUeEc()?->isChoix() === true && $ue->getUeParent() === null) {
                if ($ue->getUeEnfants()->count() === 0) {
                    //on ajoute par défaut deux UE enfants
                    $ueEnfant1 = new Ue();
                    $ueEnfant1->setSemestre($semestre);
                    $ueEnfant1->setNatureUeEc($ueObligatoire);
                    $ueEnfant1->setOrdre(1);
                    $ueEnfant1->setUeParent($ue);
                    $ueEnfant1->setLibelle('Choix 1');
                    $ueRepository->save($ueEnfant1, true);

                    $ueEnfant2 = new Ue();
                    $ueEnfant2->setSemestre($semestre);
                    $ueEnfant2->setNatureUeEc($ueObligatoire);
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

            return $turboStream->stream('parcours_v2/turbo/add_ue_success.stream.html.twig', [
                'parcours' => $parcours,
                'semestreParcours' => $semestreParcours,
                'semestre' => $dtoSemestre,
                'toastMessage' => $toastMessage,
                'newUeId' => $ue->getId(),
            ]);
        }

        return $turboStream->streamOpenModalFromTemplates(
            'Ajouter une UE',
            'Dans : semestre ' . $semestreParcours->getSemestre()?->getOrdre(),
            'parcours_v2/ue/_new.html.twig',
            [
                'form' => $form->createView(),
            ],
            '_ui/_footer_submit_cancel.html.twig',
            [
                'submitLabel' => 'Créer l\'UE',
            ]
        );
    }

    #[Route('/{parcours}/semestre/{semestreParcours}/{ue}/modifier', name: '_edit_ue_semestre', methods: ['GET', 'POST'])]
    public function edit(
        TurboStreamResponseFactory $turboStreamResponseFactory,
        TypeDiplomeResolver        $typeDiplomeResolver,
        Request                    $request,
        Ue                         $ue,
        Parcours                   $parcours,
        UeRepository               $ueRepository,
        SemestreParcours           $semestreParcours
    ): Response
    {
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();
        if ($typeDiplome === null) {
            throw new \RuntimeException('Type de diplôme non trouvé');
        }
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $form = $this->createForm(UeType::class, $ue, [
            'action' => $this->generateUrl('parcours_structure_edit_ue_semestre', [
                'ue' => $ue->getId(),
                'parcours' => $parcours->getId(),
                'semestreParcours' => $semestreParcours->getId(),
            ]),
            'typeDiplome' => $typeDiplome
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('natureUeEc')->getData() !== null) {
                if ($form->get('natureUeEc')->getData()->isChoix() === true) {
                    if ($ue->getUeEnfants()->count() === 0) {
                        $ue1 = clone $ue;
                        $ue1->setUeOrigineCopie(null);
                        $ue1->setUeParent($ue);
                        $ue1->setOrdre(1);
                        $ueRepository->save($ue1, true);

                        $ue2 = clone $ue;
                        $ue2->setUeOrigineCopie(null);
                        $ue2->setUeParent($ue);
                        $ue2->setOrdre(2);
                        $ueRepository->save($ue2, true);
                    }
                } else {
                    foreach ($ue->getUeEnfants() as $ueEnfant) {
                        $ueRepository->remove($ueEnfant, true);
                    }
                }
            }

            $ueRepository->save($ue, true);

            // recalculer le DTO du semestre pour renvoyer le fragment mis à jour
            $typeD = $typeDiplomeResolver->fromParcours($parcours);
            $dtoSemestre = $typeD->calculStructureSemestre($semestreParcours, $parcours);

            return $turboStreamResponseFactory->stream('parcours_v2/turbo/add_ue_success.stream.html.twig', [
                'parcours' => $parcours,
                'semestreParcours' => $semestreParcours,
                'semestre' => $dtoSemestre,
                'toastMessage' => 'UE modifiée avec succès',
                'newUeId' => $ue->getId(),
            ]);
        }

        return $turboStreamResponseFactory->streamOpenModalFromTemplates(
            'Modifier l\'' . $ue->display(),
            'Dans : le semestre  ' . $ue->getSemestre()?->display(),
            'parcours_v2/ue/_new.html.twig',
            [
                'form' => $form->createView(),
            ],
            '_ui/_footer_submit_cancel.html.twig',
            ['submitLabel' => 'Modifier']
        );
    }

    #[Route('/{parcours}/semestre/{semestreParcours}/{ue}/pre-delete-ue', name: '_pre_delete_ue_semestre')]
    public function preDeleteUe(
        TurboStreamResponseFactory $turboStream,
        CsrfTokenManagerInterface  $csrfTokenManager,
        SemestreParcours           $semestreParcours,
        Parcours                   $parcours,
        Ue                         $ue
    ): Response
    {

        return $turboStream->streamOpenModalFromTemplates(
            'Supprimer une UE',
            'Dans : semestre ' . $semestreParcours->getSemestre()?->getOrdre(),
            'parcours_v2/ue/_delete.html.twig',
            [],
            '_ui/_footer_delete_cancel.html.twig',
            [
                'submitLabel' => 'Supprimer l\'UE',
                'url' => $this->generateUrl('parcours_structure_delete_ue_semestre', [
                    'semestreParcours' => $semestreParcours->getId(),
                    'parcours' => $parcours->getId(),
                    'ue' => $ue->getId()
                ]),
                'csrf_token' => $csrfTokenManager->getToken('delete' . $ue->getId()),
            ]
        );
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
            'delete' . $ue->getId(),
            $request->query->get('csrf_token')
        )) {
            $typeD = $typeDiplomeResolver->fromParcours($parcours);
            $dtoSemestre = $typeD->calculStructureSemestre($semestreParcours, $parcours);

            //todo: supprimer l'UE, les UE enfants, les EC associés, renuméroter
            //faire un service avec renumérotation en option, pourra servir pour la suppression d'un semestre ou d'un aprcours

            return $turboStream->stream('parcours_v2/turbo/add_ue_success.stream.html.twig', [
                'parcours' => $parcours,
                'semestreParcours' => $semestreParcours,
                'semestre' => $dtoSemestre,
                'toastMessage' => 'UE supprimée avec succès',
                'newUeId' => $ue->getId(),
            ]);
        }

        return $turboStream->streamToastError(
            'Erreur lors de la suppression de l\'UE',
        );
    }
}
