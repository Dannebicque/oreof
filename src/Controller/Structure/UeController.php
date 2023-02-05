<?php

namespace App\Controller\Structure;

use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\TypeUe;
use App\Entity\Ue;
use App\Form\UeType;
use App\Repository\TypeEnseignementRepository;
use App\Repository\TypeUeRepository;
use App\Repository\UeRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/structure/ue', name: 'structure_ue_')
]
class UeController extends AbstractController
{
    #[
        Route('/detail/semestre/{semestre}/{parcours}', name: 'detail_semestre')
    ]
    public function detailComposante(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        UeRepository $ueRepository,
        TypeUeRepository $typeUeRepository,
        TypeEnseignementRepository $typeEnseignementRepository,
        Semestre $semestre,
        Parcours $parcours
    ): Response {
        $ues = $ueRepository->findBy(['semestre' => $semestre]);
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($parcours->getFormation()?->getTypeDiplome());
        return $this->render('structure/ue/_liste.html.twig', [
            'ues' => $ues,
            'semestre' => $semestre,
            'typeUes' => $typeUeRepository->findAll(), //todo: filtrer selon le type de diplôme
            'typeEnseignements' => $typeEnseignementRepository->findAll(),
            'parcours' => $parcours,
            'typeDiplome' => $typeDiplome,
        ]);
    }

    #[
        Route('/add-ue/semestre/{semestre}/{parcours}', name: 'add_ue_semestre')
    ]
    public function addUe(
        Request $request,
        UeRepository $ueRepository,
        Semestre $semestre,
        Parcours $parcours
    ): Response {
        $ue = new Ue();
        $ue->setSemestre($semestre);
        $form = $this->createForm(UeType::class, $ue, [
            'action' => $this->generateUrl('structure_ue_add_ue_semestre', [
                'semestre' => $semestre->getId(),
                'parcours' => $parcours->getId()
            ])
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ueRepository->save($ue, true);

            return $this->json(true);
        }

        return $this->render('structure/ue/_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ue/update/typeUe/{ue}', name: 'change_type_ue', methods: ['POST'])]
    public function updateTypeUe(
        Request $request,
        UeRepository $ueRepository,
        TypeUeRepository $typeUeRepository,
        Ue $ue,
    ): Response {
        $typeUe = JsonRequest::getValueFromRequest($request, 'value');

        if ($typeUe !== '') {
            $typeUe = $typeUeRepository->find($typeUe);
            $ue->setTypeUe($typeUe);
        } else {
            $ue->setTypeUe(null);
        }

        $ueRepository->save($ue, true);

        return $this->json(true);

    }

    #[Route('/ue/update/obligatoire/{ue}', name: 'change_ue_obligatoire', methods: ['POST'])]
    public function updateUeObligatoire(
        Request $request,
        UeRepository $ueRepository,
        TypeEnseignementRepository $typeEnseignementRepository,
        Ue $ue,
    ): Response {
        $typeUe = JsonRequest::getValueFromRequest($request, 'value');
        if ($typeUe !== '') {
            $typeUe = $typeEnseignementRepository->find($typeUe);
            dump($typeUe);
            $ue->setUeObligatoire($typeUe);
        } else {
            $ue->setUeObligatoire(null);
        }

        $ueRepository->save($ue, true);

        return $this->json(true);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        Ue $ue,
        UeRepository $ueRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $ue->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf'))) {
            //todo: supprimer les EC
            $ueRepository->remove($ue, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
