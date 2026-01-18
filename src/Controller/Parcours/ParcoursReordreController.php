<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Parcours/ParcoursReordreController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 18/01/2026 08:47
 */

namespace App\Controller\Parcours;

use App\Controller\BaseController;
use App\Repository\SemestreRepository;
use App\Repository\UeRepository;
use App\Repository\ElementConstitutifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/parcours/v2/structure/api', name: 'parcours_structure_api')]
class ParcoursReordreController extends BaseController
{
    #[Route('/semestres/{id}/reorder-ue', name: '_semestre_reorder_ue', methods: ['PATCH'])]
    public function reorderUe(
        int                    $id,
        Request                $request,
        SemestreRepository     $semestres,
        UeRepository           $ues,
        EntityManagerInterface $em,
    ): Response
    {
        $semestre = $semestres->find($id);
        if (!$semestre) {
            return new Response('Not found', 404);
        }

        $orderUes = [];

        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $items = $data['items'] ?? [];

        foreach ($items as $row) {
            $ue = $ues->find((int)$row['id']);

            if (!$ue || $ue->getSemestre()?->getId() !== $semestre->getId()) {
                continue;
            }
            $ue->setOrdre((int)$row['position']);
            $orderUes[$row['id']] = $ue->display();
        }

        $em->flush();

        return new Response(
            $this->renderView('parcours_v2/turbo/ue_numbers.stream.html.twig', [
                'ues' => $orderUes,
                'toastMessage' => '✅ Ordre enregistré',
                'undo' => [
                    'payload' => $data,
                    'url' => $request->getPathInfo(),
                ],
            ]),
            200,
            ['Content-Type' => 'text/vnd.turbo-stream.html']
        );
    }

    #[Route('/ue/{id}/reorder-ec', name: '_ue_reorder_ec', methods: ['PATCH'])]
    public function reorderEc(
        int                          $id,
        Request                      $request,
        UeRepository                 $ues,
        ElementConstitutifRepository $ecs,
        EntityManagerInterface       $em,
    ): Response
    {
        $ue = $ues->find($id);
        if (!$ue) {
            return new Response('Not found', 404);
        }

        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $items = $data['items'] ?? [];

        // IMPORTANT : si déplacement inter-UE, le conteneur cible est /api/ue/{id}/reorder-ec (donc $ue = nouvelle UE)
        foreach ($items as $row) {
            $ec = $ecs->find((int)$row['id']);
            if (!$ec) {
                continue;
            }

            $ec->setUe($ue); // permet “UE d’appartenance”
            $ec->setOrdre((int)$row['position']);
        }

        $em->flush();

        return $this->toastStream(
            'Ordre des EC mis à jour',
            ['payload' => $data, 'url' => $request->getPathInfo()]
        );
    }

    private function toastStream(string $message, ?array $undo = null, int $timeout = 3500): Response
    {
        return new Response(
            $this->renderView('turbo/toast.stream.html.twig', [
                'message' => $message,
                'timeout' => $timeout,
                'undo' => $undo, // null = toast simple
            ]),
            200,
            ['Content-Type' => 'text/vnd.turbo-stream.html']
        );
    }

    #[Route('/reorder/undo', name: '_reorder_undo', methods: ['POST'])]
    public function undo(Request $request, EntityManagerInterface $em, UeRepository $ues, ElementConstitutifRepository $ecs): Response
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // On reçoit previousItems + type + targetUrl (optionnel)
        $type = $data['type'] ?? null;
        $previous = $data['previousItems'] ?? [];
        $targetId = $data['targetId'] ?? null;

        if ($type === 'ue') {
            foreach ($previous as $row) {
                $ue = $ues->find((int)$row['id']);
                if ($ue) {
                    $ue->setOrdre((int)$row['position']);
                }
            }
        } elseif ($type === 'ec') {
            // Pour undo inter-UE, il faudrait aussi stocker l’ancienne UE par EC.
            // Version simple: on rétablit seulement les positions dans le conteneur cible.
            foreach ($previous as $row) {
                $ec = $ecs->find((int)$row['id']);
                if ($ec) {
                    $ec->setOrdre((int)$row['position']);
                }
            }
        } else {
            return new Response('Bad request', 400);
        }

        $em->flush();

        return $this->toastStream(
            '✅ Annulation effectuée'
        );
    }
}
