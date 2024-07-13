<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Process/ParcoursProcess.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/09/2023 10:46
 */

namespace App\Classes\Process;

use App\Classes\JsonReponse;
use App\Classes\verif\ParcoursValide;
use App\DTO\ProcessData;
use App\Entity\DpeParcours;
use App\Events\HistoriqueParcoursEvent;
use App\Utils\Tools;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\RuntimeException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ParcoursProcess extends AbstractProcess
{
    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        private WorkflowInterface  $dpeParcoursWorkflow
    ) {
        parent::__construct($entityManager, $eventDispatcher, $translator);
    }

    public function etatParcours(DpeParcours $dpeParcours, $process): ProcessData
    {
        $parcours = $dpeParcours->getParcours();

        if ($parcours === null) {
            throw new RuntimeException('Parcours non trouvé');
        }

        $formation = $parcours->getFormation();
        $processData = new ProcessData();
        $processData->definition = $this->dpeParcoursWorkflow->getDefinition();

        if (array_key_exists('check', $process) && $formation !== null) {
            $parcoursValide = new ParcoursValide($parcours, $formation->getTypeDiplome());
            $processData->validation['parcours'] = $parcoursValide->valideParcours();
            $processData->validation['fiches'] = $parcoursValide->valideFichesParcours($process);
            $processData->valid = $parcoursValide->isParcoursValide();
        }

        $processData->place = $this->dpeParcoursWorkflow->getMarking($dpeParcours);
        $processData->transitions = $this->dpeParcoursWorkflow->getEnabledTransitions($dpeParcours);

        return $processData;
    }

    public function valideParcours(DpeParcours $dpeParcours, UserInterface $user, $process, $etape, $request, ?string $fileName = null): Response
    {

        $valid = $process['canValide'];
        $motifs = [];

        if ($request->request->has('date')) {
            $motifs['date'] = Tools::convertDate($request->request->get('date'));
        }

        if ($request->request->has('argumentaire')) {
            $motifs['motif'] = $request->request->get('argumentaire', '');
        }

        if ($request->request->has('acceptationDirecte')) {
            $motifs['acceptationDirecte'] = $request->request->get('acceptationDirecte') === 'acceptationDirecte';
            $valid = 'valider_cfvu';
        }

        if ($request->request->has('sousReserveConseil')) {
            $motifs['sousReserveConseil'] = (bool)$request->request->get('sousReserveConseil');
            $valid = 'valider_reserve_conseil_cfvu';
        }

        if ($request->request->has('sousReserveModifications')) {
            $motifs['sousReserveModifications'] = (bool)$request->request->get('sousReserveModifications');
            $valid = 'valider_reserve_cfvu';

            if ($request->request->has('argumentaire_sousReserveModifications')) {
                $motifs['argumentaire_sousReserveModifications'] = $request->request->get('argumentaire_sousReserveModifications');
            }
        }


        $this->dpeParcoursWorkflow->apply($dpeParcours, $valid, $motifs);
        $this->entityManager->flush();
        return $this->dispatchEventParcours($dpeParcours, $user, $etape, $request, 'valide', $fileName);
    }

    public function refuseParcours(DpeParcours $dpeParcours, UserInterface $user, $process, $etape, $request): Response
    {
        $reponse = $this->dispatchEventParcours($dpeParcours, $user, $etape, $request, 'refuse');

        $motifs = [];
        $refus = $process['canRefuse'];

        if ($request->request->has('date')) {
            $motifs['date'] = Tools::convertDate($request->request->get('date'));
        }

        if ($request->request->has('etatRefus')) {
            $motifs['etatRefus'] = $request->request->get('etatRefus');

            if ($motifs['etatRefus'] === 'projetRefusDefinitif') {
                $refus = 'refuser_definitif_cfvu';
            } elseif ($motifs['etatRefus'] === 'projetARevoir') {
                $refus = 'refuser_revoir_cfvu';
            }

            if ($request->request->has('argumentaire')) {
                $motifs['motif'] = $request->request->get('argumentaire');
            }
        }

        $this->dpeParcoursWorkflow->apply($dpeParcours, $refus, $motifs);
        $this->entityManager->flush();

        return $reponse;
    }

    public function reserveParcours(DpeParcours $dpeParcours, UserInterface $user, $process, $etape, $request): Response
    {
        $this->dpeParcoursWorkflow->apply($dpeParcours, $process['canReserve'], ['motif' => $request->request->get('argumentaire')]);
        $this->entityManager->flush();
        return $this->dispatchEventParcours($dpeParcours, $user, $etape, $request, 'reserve');
    }

    private function dispatchEventParcours(DpeParcours $dpeParcours, UserInterface $user, string $etape, Request $request, string $etat, ?string $fileName = null): Response
    {
        $histoEvent = new HistoriqueParcoursEvent($dpeParcours->getParcours(), $user, $etape, $etat, $request, $fileName);
        $this->eventDispatcher->dispatch($histoEvent, HistoriqueParcoursEvent::ADD_HISTORIQUE_PARCOURS);
        return JsonReponse::success($this->translator->trans('parcours.'.$etat.'.' . $etape . '.flash.success', [], 'process'));
    }
}
