<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/AdminEditController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 13/06/2025 13:17
 */

namespace App\Controller;

use App\Classes\ValidationProcess;
use App\Enums\TypeModificationDpeEnum;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/edit', name: 'app_admin_edit')]
class AdminEditController extends BaseController
{

    public function __construct(
        private readonly ValidationProcess $validationProcess
    )
    {
    }

    #[Route('/{id}/{type}', name: '_modal')]
    public function afficheModal(
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        int    $id,
        string $type,
    ): Response
    {
        switch ($type) {
            case 'parcours':
                $object = $parcoursRepository->find($id);
                if (!$object) {
                    throw $this->createNotFoundException('Parcours not found');
                }
                break;
            case 'formation':
                $object = $formationRepository->find($id);
                if (!$object) {
                    throw $this->createNotFoundException('Formation not found');
                }
                break;
            default:
                throw $this->createNotFoundException('Invalid type');
        }


        return $this->render('admin/edit/_modal.html.twig', [
            'id' => $id,
            'type' => $type,
            'object' => $object,
            'etats' => $this->validationProcess->getProcess(),
            'typesModifs' => TypeModificationDpeEnum::cases()
        ]);
    }
}

