<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Structure/UeController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Structure;

use App\Classes\UeOrdre;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\NatureUeEc;
use App\Entity\TypeUe;
use App\Entity\Ue;
use App\Form\UeType;
use App\Repository\NatureUeEcRepository;
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
        NatureUeEcRepository $natureUeEcRepository,
        Semestre $semestre,
        Parcours $parcours
    ): Response {
        $ues = $ueRepository->findBy(['semestre' => $semestre], ['ordre' => 'ASC', 'subOrdre' => 'ASC']);
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        return $this->render('structure/ue/_liste.html.twig', [
            'ues' => $ues,
            'semestre' => $semestre,
            'typeUes' => $typeDiplome?->getTypeUes(),
            'natureUeEcs' => $natureUeEcRepository->findAll(),
            'parcours' => $parcours,
            'typeDiplome' => $typeDiplome,
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[
        Route('/add-ue/semestre/{semestre}/{parcours}', name: 'add_ue_semestre')
    ]
    public function addUe(
        NatureUeEcRepository $natureUeEcRepository,
        TypeUeRepository $typeUeRepository,
        Request $request,
        UeRepository $ueRepository,
        Semestre $semestre,
        Parcours $parcours
    ): Response {
        $ue = new Ue();
        $ue->setSemestre($semestre);
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        if ($typeDiplome === null) {
            throw new \Exception('Type de diplôme non trouvé');
        }
        $form = $this->createForm(UeType::class, $ue, [
            'action' => $this->generateUrl('structure_ue_add_ue_semestre', [
                'semestre' => $semestre->getId(),
                'parcours' => $parcours->getId()
            ]),
            'choices' => $typeDiplome?->getTypeUes(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ordre = $ueRepository->getMaxOrdre($semestre);
            $ue->setOrdre($ordre + 1);

            if ($form->get('typeUe')->getData() !== null) {
                $tu = $typeUeRepository->find($form->get('typeUe')->getData());
                $ue->setTypeUe($tu);
            }

            if ($form->get('typeUeTexte')->getData() !== null && $form->get('typeUe')->getData() === null) {
                $tu = new TypeUe();
                $tu->setLibelle($form->get('typeUeTexte')->getData());
                $tu->addTypeDiplome($typeDiplome);
                $typeUeRepository->save($tu, true);
                $ue->setTypeUe($tu);
            }

            if ($form->get('natureUeEcTexte')->getData() === null && $form->get('natureUeEc')->getData() !== null) {
                if ($form->get('natureUeEc')->getData()->isChoix() === true) {
                    $ue->setSubOrdre(1);
                    $ue2 = clone $ue;
                    $ue2->setSubOrdre(2);
                    $ueRepository->save($ue2, true);
                }
            }

            if ($form->get('natureUeEcTexte')->getData() !== null && $form->get('natureUeEc')->getData() === null) {
                $tu = new NatureUeEc();
                $tu->setLibelle($form->get('natureUeEcTexte')->getData());
                $natureUeEcRepository->save($tu, true);
                $ue->setNatureUeEc($tu);
            }

            //todo: gérer le cas ou la nature d'UE est multiple ????

            $ueRepository->save($ue, true);

            return $this->json(true);
        }

        return $this->render('structure/ue/_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws \JsonException
     */
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

    /**
     * @throws \JsonException
     */
    #[Route('/ue/update/nature/{ue}', name: 'change_nature_ue', methods: ['POST'])]
    public function updateNatureUe(
        Request $request,
        UeRepository $ueRepository,
        NatureUeEcRepository $natureUeEcRepository,
        Ue $ue,
    ): Response {
        $idNatureUe = JsonRequest::getValueFromRequest($request, 'value');
        if ($idNatureUe !== '') {
            $natureUe = $natureUeEcRepository->find($idNatureUe);
            if ($natureUe !== null) {
                $ue->setNatureUeEc($natureUe);
                if ($natureUe->isChoix() === true) {
                    $ue->setSubOrdre(1);
                    $ue2 = clone $ue;
                    $ue2->setSubOrdre(2);
                    $ueRepository->save($ue2, true);
                }
            }
        } else {
            $ue->setNatureUeEc(null);//todo: gérer ??
        }

        $ueRepository->save($ue, true);

        return $this->json(true);
    }

    #[Route('/deplacer/{ue}/{sens}', name: 'deplacer', methods: ['GET'])]
    public function deplacer(
        UeOrdre $ueOrdre,
        Ue $ue,
        string $sens
    ): Response {
        $ueOrdre->deplacerUe($ue, $sens);

        return $this->json(true);
    }

    #[Route('/deplacer_sub/{ue}/{sens}', name: 'deplacer_sub', methods: ['GET'])]
    public function deplacerSub(
        UeOrdre $ueOrdre,
        Ue $ue,
        string $sens
    ): Response {
        $ueOrdre->deplacerSubUe($ue, $sens);

        return $this->json(true);
    }


    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        Ue $ue,
        UeRepository $ueRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $ue->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            //todo: supprimer les EC
            $ueRepository->remove($ue, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
