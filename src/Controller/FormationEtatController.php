<?php

namespace App\Controller;

use App\Repository\FormationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/central/formation/etat')]
class FormationEtatController extends BaseController
{
    public function __construct(private readonly WorkflowInterface $dpeWorkflow)
    {}
    #[Route('/send/ouverture', name: 'app_formation_etat_send_ouverture')]
    public function sendOuverture(
        FormationRepository $formationRepository
    ): Response
    {
        $formations = $formationRepository->findBy(['anneeUniversitaire' => $this->getAnneeUniversitaire()]);
        $listeFormationsOuvrables = [];
        foreach ($formations as $formation) {
            if ($this->dpeWorkflow->can($formation, 'initialiser')) {
                if (!array_key_exists($formation->getComposantePorteuse()?->getId(), $listeFormationsOuvrables)) {
                    $listeFormationsOuvrables[$formation->getComposantePorteuse()?->getId()] = [];
                }
                $listeFormationsOuvrables[$formation->getComposantePorteuse()?->getId()][] = $formation;
                $this->dpeWorkflow->apply($formation, 'initialiser');
                $formationRepository->save($formation, true);
                //todo: initialiser également la composante dans ce cas ?
            }
        }

        //envoi d'un mail de synthèse aux composantes porteuses
        //todo: à faire

        $this->addFlashBag('success', 'Les formations ont été ouvertes');

        return $this->redirectToRoute('structure_formation_index');
    }
}
