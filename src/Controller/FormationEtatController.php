<?php

namespace App\Controller;

use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/central/formation/etat')]
class FormationEtatController extends BaseController
{
    public function __construct(private WorkflowInterface $dpeWorkflow)
    {}
    #[Route('/send/ouverture', name: 'app_formation_etat_send_ouverture')]
    public function sendOuverture(
        FormationRepository $formationRepository
    ): Response
    {
        $formations = $formationRepository->findBy(['anneeUniversitaire' => $this->getAnneeUniversitaire()]);

        foreach ($formations as $formation) {
            if ($this->dpeWorkflow->can($formation, 'initialiser')) {
                $this->dpeWorkflow->apply($formation, 'initialiser');
                $formationRepository->save($formation, true);
            }
        }

        return $this->redirectToRoute('structure_formation_index');
    }
}
