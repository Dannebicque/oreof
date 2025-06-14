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
        int    $id,
        string $type,
    ): Response
    {
        return $this->render('admin/edit/_modal.html.twig', [
            'id' => $id,
            'type' => $type,
            'etats' => $this->validationProcess->getProcess(),
        ]);
    }
}

