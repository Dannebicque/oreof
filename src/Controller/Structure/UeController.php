<?php

namespace App\Controller\Structure;

use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\TypeUe;
use App\Entity\Ue;
use App\Repository\TypeEnseignementRepository;
use App\Repository\TypeUeRepository;
use App\Repository\UeRepository;
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
        UeRepository $ueRepository,
        TypeUeRepository $typeUeRepository,
        TypeEnseignementRepository $typeEnseignementRepository,
        Semestre $semestre,
        Parcours $parcours
    ): Response {
        $ues = $ueRepository->findBy(['semestre' => $semestre]);

        return $this->render('structure/ue/_liste.html.twig', [
            'ues' => $ues,
            'typeUes' => $typeUeRepository->findAll(), //todo: filtrer selon le type de diplôme
            'typeEnseignements' => $typeEnseignementRepository->findAll(),
            'parcours' => $parcours
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
}
