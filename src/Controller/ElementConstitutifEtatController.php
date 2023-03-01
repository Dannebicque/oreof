<?php

namespace App\Controller;

use App\Entity\ElementConstitutif;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FormationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/formation/elements-constitutifs/etat')]
class ElementConstitutifEtatController extends BaseController
{
    public function __construct(private readonly WorkflowInterface $ecWorkflow)
    {
    }

    #[Route('/send/ouverture', name: 'app_ec_etat_send_ouverture')]
    public function sendOuverture(
        ElementConstitutifRepository $elementConstitutifRepository
    ): Response {
        $ecs = $elementConstitutifRepository->findByResponsableFormation($this->getUser(),
            $this->getAnneeUniversitaire());
        foreach ($ecs as $ec) {
            if ($this->ecWorkflow->can($ec, 'initialiser')) {
                $this->ecWorkflow->apply($ec, 'initialiser');
                $elementConstitutifRepository->save($ec, true);
            }
        }

        $this->addFlashBag('success', 'Les EC ont été ouverts et les responsables de ces EC informés');

        return $this->redirectToRoute('structure_ec_index');
    }

    #[Route('/valide/{id}/{parcours}', name: 'app_ec_etat_valide')]
    public function valide(
        ElementConstitutifRepository $elementConstitutifRepository,
        ElementConstitutif $ec,
    ): Response {

        if ($this->ecWorkflow->can($ec, 'valider_ec')) {
            $this->ecWorkflow->apply($ec, 'valider_ec');
            $elementConstitutifRepository->save($ec, true);
        }


        $this->addFlashBag('success', 'L\'EC a été validé');

        return $this->redirectToRoute('structure_ec_index');
    }
}
