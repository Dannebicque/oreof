<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Process/FormationProcess.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/09/2023 10:46
 */

namespace App\Classes\Process;

use App\Classes\JsonReponse;
use App\Classes\verif\FormationValide;
use App\DTO\ProcessData;
use App\Entity\Formation;
use App\Events\HistoriqueFormationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FormationProcess extends AbstractProcess
{

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        private WorkflowInterface  $dpeWorkflow
    ) {
        parent::__construct($entityManager, $eventDispatcher, $translator);
    }

    public function etatFormation(Formation $formation, $process): ProcessData
    {
        $processData = new ProcessData();
        $processData->definition = $this->dpeWorkflow->getDefinition();

        if (array_key_exists('check', $process)) {
            $formationValide = new FormationValide($formation);
            $processData->validation['parcours'] = $formationValide->valideParcours($process);
            $processData->validation['formation'] = $formationValide->valideFormation();
            $processData->valid = $formationValide->isFormationValide();
        }

        $processData->place = $this->dpeWorkflow->getMarking($formation);
        $processData->transitions = $this->dpeWorkflow->getEnabledTransitions($formation);

        return $processData;
    }

    public function valideFormation(Formation $formation, UserInterface $user, $process, $etape, $request): Response
    {
        $this->dpeWorkflow->apply($formation, $process['canValide']);
        $this->entityManager->flush();
        return $this->dispatchEventFormation($formation, $user, $etape, $request, 'valide');
    }

    public function reserveFormation(Formation $formation, UserInterface $user, $process, $etape, $request): Response
    {
        $this->dpeWorkflow->apply($formation, $process['canReserve'], ['motif' => $request->request->get('argumentaire')]);
        $this->entityManager->flush();
        return $this->dispatchEventFormation($formation, $user, $etape, $request, 'reserve');
    }

    public function refuseFormation(Formation $formation, UserInterface $user, $process, $etape, $request): Response
    {
        $this->dpeWorkflow->apply($formation, $process['canRefuse'], ['motif' => $request->request->get('argumentaire')]);
        $this->entityManager->flush();
        return $this->dispatchEventFormation($formation, $user, $etape, $request, 'refuse');
    }

    private function dispatchEventFormation(Formation $formation, UserInterface $user, string $etape, Request $request, string $etat): Response
    {
        $histoEvent = new HistoriqueFormationEvent($formation, $user, $etape, $etat, $request);
        $this->eventDispatcher->dispatch($histoEvent, HistoriqueFormationEvent::ADD_HISTORIQUE_FORMATION);
        return JsonReponse::success($this->translator->trans('formation.'.$etat.'.' . $etape . '.flash.success', [], 'process'));
    }
}
