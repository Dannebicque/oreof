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
use App\Entity\Historique;
use App\Entity\HistoriqueFormation;
use App\Events\HistoriqueFormationEditEvent;
use App\Events\HistoriqueFormationEvent;
use App\Utils\Tools;
use DateTime;
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
        EntityManagerInterface    $entityManager,
        EventDispatcherInterface  $eventDispatcher,
        TranslatorInterface       $translator,
        private WorkflowInterface $dpeWorkflow
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
        $reponse = $this->dispatchEventFormation($formation, $user, $etape, $request, 'valide');

        $motifs = [];

        //                refuser_definitif_cfvu:
        //                    from: 'soumis_cfvu'
        //                    to: 'refuse_definitif_cfvu'
        //                refuser_revoir_cfvu:
        //                    from: 'soumis_cfvu'
        //                    to: 'en_cours_redaction'


        $valid = $process['canValide'];

        //todo: une partie plus utile ?

        if ($request->request->has('date')) {
            $motifs['date'] = Tools::convertDate($request->request->get('date'));
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

        $this->dpeWorkflow->apply($formation, $valid, $motifs);

        $this->entityManager->flush();

        return $reponse;
    }

    public function editFormation(Historique|HistoriqueFormation $historiqueFormation, UserInterface $user, $etape, $request): Response
    {
        $reponse = $this->dispatchEventEditFormation($historiqueFormation, $user, $etape, $request, 'valide');
        return $reponse;
    }

    public function reserveFormation(Formation $formation, UserInterface $user, $process, $etape, $request): Response
    {
        $reponse = $this->dispatchEventFormation($formation, $user, $etape, $request, 'reserve');

        $this->dpeWorkflow->apply($formation, $process['canReserve'], ['motif' => $request->request->get('argumentaire')]);
        //todo: pas dans la table DPE...
        $this->entityManager->flush();

        return $reponse;
    }

    public function refuseFormation(Formation $formation, UserInterface $user, $process, $etape, $request): Response
    {
        $reponse = $this->dispatchEventFormation($formation, $user, $etape, $request, 'refuse');

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

        $this->dpeWorkflow->apply($formation, $refus, $motifs);
        $this->entityManager->flush();

        return $reponse;
    }

    private function dispatchEventFormation(Formation $formation, UserInterface $user, string $etape, Request $request, string $etat): Response
    {
        $histoEvent = new HistoriqueFormationEvent($formation, $user, $etape, $etat, $request);
        $this->eventDispatcher->dispatch($histoEvent, HistoriqueFormationEvent::ADD_HISTORIQUE_FORMATION);
        return JsonReponse::success($this->translator->trans('formation.' . $etat . '.' . $etape . '.flash.success', [], 'process'));
    }

    private function dispatchEventEditFormation(HistoriqueFormation $historiqueFormation, UserInterface $user, $etape, $request, string $etat): Response
    {
        $histoEvent = new HistoriqueFormationEditEvent($historiqueFormation, $user, $etape, $etat, $request);
        $this->eventDispatcher->dispatch($histoEvent, HistoriqueFormationEditEvent::EDIT_HISTORIQUE_FORMATION);
        return JsonReponse::success($this->translator->trans('formation.edit.' . $etat . '.' . $etape . '.flash.success', [], 'process'));
    }
}
