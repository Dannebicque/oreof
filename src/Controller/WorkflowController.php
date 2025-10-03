<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/WorkflowController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 03/10/2025 12:59
 */

// src/Controller/WorkflowController.php
namespace App\Controller;

use App\Repository\DpeParcoursRepository;
use App\Workflow\WorkflowActionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/api/workflow')]
class WorkflowController extends AbstractController
{
    #[Route('/dpe-parcours/{id}/{transition}', name: 'api_dpe_workflow_apply', methods: ['POST'])]
    public function apply(
        int                   $id,
        string                $transition,
        Request               $request,
        DpeParcoursRepository $repo,
        WorkflowActionService $actions
    ): JsonResponse
    {
        $subject = $repo->find($id);
        if (!$subject) {
            return $this->json(['error' => 'Not found'], 404);
        }

        // data = { intituleCourt, ects, volumeCM, comment?, files?, date? ... }
        // (si upload multipart, utilise $request->files)
        $data = $request->request->all();
        $files = $request->files->all();
        if (!empty($files)) {
            // Aplatit les fichiers dans $data['files']
            $data['files'] = [];
            foreach ($files as $key => $value) {
                if (\is_array($value)) {
                    $data['files'] = array_merge($data['files'], $value);
                } else {
                    $data['files'][] = $value;
                }
            }
        }

        $actions->apply($subject, 'dpeParcours', $transition, $data);

        return $this->json(['status' => 'ok']);
    }
}
