<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Process/FicheMatiereProcess.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/09/2023 10:46
 */

namespace App\Classes\Process;

use App\Classes\JsonReponse;
use App\Classes\verif\FicheMatiereValide;
use App\DTO\ProcessData;
use App\Entity\FicheMatiere;
use App\Entity\User;
use App\Events\HistoriqueFicheMatiereEvent;
use App\Repository\FicheMatiereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FicheMatiereProcess extends AbstractProcess
{
    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        #[Target('fiche')]
        private WorkflowInterface             $ficheMatiereWorkflow,
    ) {
        parent::__construct($entityManager, $eventDispatcher, $translator);
    }

    public function etatFicheMatiere(FicheMatiere $ficheMatiere, $process): ProcessData
    {
        $formation = $ficheMatiere->getParcours()?->getFormation();
        $processData = new ProcessData();
        $processData->definition = $this->ficheMatiereWorkflow->getDefinition();

        if (array_key_exists('check', $process) && $formation !== null) {
            $ficheMatiereValide = new FicheMatiereValide($ficheMatiere, $formation->getTypeDiplome());
            $processData->validation['ficheMatiere'] = $ficheMatiereValide->valideFicheMatiere();
            $processData->valid = $ficheMatiereValide->isFicheMatiereValide();
        }

        $processData->place = $this->ficheMatiereWorkflow->getMarking($ficheMatiere);
        $processData->transitions = $this->ficheMatiereWorkflow->getEnabledTransitions($ficheMatiere);

        return $processData;
    }

    public function valideFicheMatiere(FicheMatiere $ficheMatiere, UserInterface $user, $process, $etape, $request): Response
    {
        $this->ficheMatiereWorkflow->apply($ficheMatiere, $process['canValide']);
        $this->entityManager->flush();
        return $this->dispatchEventFicheMatiere($ficheMatiere, $user, $etape, $request, 'valide');
    }

    public function reserveFicheMatiere(FicheMatiere $ficheMatiere, UserInterface $user, $process, $etape, $request): Response
    {
        $this->ficheMatiereWorkflow->apply($ficheMatiere, $process['canReserve'], ['motif' => $request->request->get('argumentaire')]);
        $this->entityManager->flush();
        return $this->dispatchEventFicheMatiere($ficheMatiere, $user, $etape, $request, 'reserve');
    }

    private function dispatchEventFicheMatiere(FicheMatiere $ficheMatiere, UserInterface $user, string $etape, Request $request, string $etat): Response
    {
        //todo: mail uniquement si parcours complet
        $histoEvent = new HistoriqueFicheMatiereEvent($ficheMatiere, $user, $etape, $etat, $request);
        $this->eventDispatcher->dispatch($histoEvent, HistoriqueFicheMatiereEvent::ADD_HISTORIQUE_FICHE_MATIERE);
        return JsonReponse::success($this->translator->trans('ficheMatiere.'.$etat.'.' . $etape . '.flash.success', [], 'process'));
    }
}