<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/Components/SwitchAnneeUniversitaireComponent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/01/2023 16:27
 */

namespace App\Twig\Components;

use App\Repository\CampagneCollecteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('switch_dpe')]
final class SwitchDpeComponent extends AbstractController
{
    use DefaultActionTrait;

    public array $dpes;

    public function __construct(
        protected RequestStack $requestStack,
        CampagneCollecteRepository $dpeRepository
    )
    {
        $this->dpes = $dpeRepository->findAll();
    }

    #[LiveAction]
    public function changeDpe(#[LiveArg] int $id): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $this->requestStack->getSession()->set('campagneCollecte', $id);

        //forcer l'actualisation de la page
        return $this->redirectToRoute('app_homepage');

    }
}
