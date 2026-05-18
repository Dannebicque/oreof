<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/oreofv2/src/Controller/SwitchDpeController.php
 * @author davidannebicque
 * @project oreofv2
 * @lastUpdate 17/05/2026 11:50
 */

namespace App\Controller;

use App\Entity\CampagneCollecte;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

class SwitchDpeController extends BaseController
{

    #[Route('/switch-dpe/{id}', name: 'app_switch_dpe')]
    public function switchDpe(
        RequestStack     $requestStack,
        CampagneCollecte $id
    ): RedirectResponse
    {
        $requestStack->getSession()->set('campagneCollecte', $id);

        //forcer l'actualisation de la page
        return $this->redirectToRoute('app_homepage');
    }
}
