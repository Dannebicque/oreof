<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FormationEtatController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Repository\FormationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/central/formation/etat')]
class FormationEtatController extends BaseController
{
    public function __construct(private readonly WorkflowInterface $dpeWorkflow)
    {
    }
    #[Route('/send/ouverture', name: 'app_formation_etat_send_ouverture')]
    public function sendOuverture(
        FormationRepository $formationRepository
    ): Response {
        $formations = $formationRepository->findBy(['anneeUniversitaire' => $this->getCampagneCollecte()]);
        $listeFormationsOuvrables = [];
        foreach ($formations as $formation) {
            if ($this->dpeWorkflow->can($formation, 'initialiser') && $formation->getResponsableMention() !== null) {
                if (!array_key_exists($formation->getComposantePorteuse()?->getId(), $listeFormationsOuvrables)) {
                    $listeFormationsOuvrables[$formation->getComposantePorteuse()?->getId()] = [];
                }
                $listeFormationsOuvrables[$formation->getComposantePorteuse()?->getId()][] = $formation;
                $this->dpeWorkflow->apply($formation, 'initialiser');
                $formationRepository->save($formation, true);
            }
        }

        $this->addFlashBag('success', 'Les formations ont été ouvertes');

        return $this->redirectToRoute('structure_composante_index');
    }
}
