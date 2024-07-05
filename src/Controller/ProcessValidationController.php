<?php

namespace App\Controller;

use App\Classes\GetDpeParcours;
use App\Classes\GetHistorique;
use App\Classes\JsonReponse;
use App\Classes\Process\FicheMatiereProcess;
use App\Classes\Process\FormationProcess;
use App\Classes\Process\ParcoursProcess;
use App\Classes\ValidationProcess;
use App\Classes\ValidationProcessFicheMatiere;
use App\Entity\DpeDemande;
use App\Enums\TypeModificationDpeEnum;
use App\Events\DpeDemandeEvent;
use App\Events\HistoriqueFormationEvent;
use App\Events\HistoriqueParcoursEvent;
use App\Repository\DpeParcoursRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use App\Service\VersioningParcours;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProcessValidationController extends BaseController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface   $entityManager,
        private readonly ValidationProcess        $validationProcess,
        private readonly ValidationProcessFicheMatiere        $validationProcessFicheMatiere,
        private readonly FormationProcess         $formationProcess,
        private readonly ParcoursProcess          $parcoursProcess,
        private readonly FicheMatiereProcess          $ficheMatiereProcess,
    ) {
    }

    #[Route('/validation/valide/{etape}', name: 'app_validation_valide')]
    public function valide(
        GetHistorique       $getHistorique,
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        FicheMatiereRepository $ficheMatiereRepository,
        string              $etape,
        Request             $request
    ): Response {
        $type = $request->query->get('type');
        $id = $request->query->get('id');


        $laisserPasser = false;
        switch ($type) {
            case 'formation':
                $process = $this->validationProcess->getEtape($etape);
                $objet = $formationRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Formation non trouvée');
                }

                if ($etape === 'cfvu') {
                    $laisserPasser = $getHistorique->getHistoriqueFormationLastStep($objet, 'conseil');
                }

                $processData = $this->formationProcess->etatFormation($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->formationProcess->valideFormation($objet, $this->getUser(), $process, $etape, $request);
                }
                break;
            case 'parcours':
                $process = $this->validationProcess->getEtape($etape);
                $objet = $parcoursRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Parcours non trouvé');
                }

                $processData = $this->parcoursProcess->etatParcours($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->parcoursProcess->valideParcours($objet, $this->getUser(), $process, $etape, $request);
                }

                break;
            case 'ficheMatiere':
                $process = $this->validationProcessFicheMatiere->getEtape($etape);

                $objet = $ficheMatiereRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Fiche matière non trouvée');
                }

                $processData = $this->ficheMatiereProcess->etatFicheMatiere($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->ficheMatiereProcess->valideFicheMatiere($objet, $this->getUser(), $process, $etape, $request);
                }
                break;
        }

        return $this->render('process_validation/_valide.html.twig', [
            'objet' => $objet,
            'process' => $process,
            'type' => $type,
            'id' => $id,
            'etape' => $etape,
            'processData' => $processData ?? null,
            'laisserPasser' => $laisserPasser,
        ]);
    }

    #[Route('/validation/refuse/{etape}', name: 'app_validation_refuse')]
    public function refuse(
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        string              $etape,
        Request             $request
    ): Response {
        $type = $request->query->get('type');
        $id = $request->query->get('id');

        $process = $this->validationProcess->getEtape($etape);

        switch ($type) {
            case 'formation':
                $objet = $formationRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Formation non trouvée');
                }

                $processData = $this->formationProcess->etatFormation($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->formationProcess->refuseFormation($objet, $this->getUser(), $process, $etape, $request);
                }
                break;
            case 'parcours':
                $objet = $parcoursRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Parcours non trouvé');
                }

                $processData = $this->parcoursProcess->etatParcours($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->parcoursProcess->refuseParcours($objet, $this->getUser(), $process, $etape, $request);
                }
                break;
            case 'ficheMatiere':
                $objet = $formationRepository->find($id);
                if ($objet === null) {
                    return JsonReponse::error('Fiche EC/matière non trouvée');
                }
                //                $place = $dpeWorkflow->getMarking($objet);
                //                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                break;
        }

        return $this->render('process_validation/_refuse.html.twig', [
            'process' => $process,
            'type' => $type,
            'id' => $id,
            'etape' => $etape,
            'objet' => $objet,
            'processData' => $processData ?? null,
        ]);
    }

    #[Route('/validation/reserve/{etape}', name: 'app_validation_reserve')]
    public function reserve(
        FicheMatiereRepository $ficheMatiereRepository,
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        string              $etape,
        Request             $request
    ): Response {
        $type = $request->query->get('type');
        $id = $request->query->get('id');

        $process = $this->validationProcess->getEtape($etape);

        switch ($type) {
            case 'formation':
                $objet = $formationRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Formation non trouvée');
                }

                $processData = $this->formationProcess->etatFormation($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->formationProcess->reserveFormation($objet, $this->getUser(), $process, $etape, $request);
                }
                break;
            case 'parcours':
                $objet = $parcoursRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Parcours non trouvé');
                }

                $processData = $this->parcoursProcess->etatParcours($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->parcoursProcess->reserveParcours($objet, $this->getUser(), $process, $etape, $request);
                }
                break;
            case 'ficheMatiere':
                $process = $this->validationProcessFicheMatiere->getEtape($etape);

                $objet = $ficheMatiereRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Fiche matière non trouvée');
                }

                $processData = $this->ficheMatiereProcess->etatFicheMatiere($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->ficheMatiereProcess->reserveFicheMatiere($objet, $this->getUser(), $process, $etape, $request);
                }
                break;
        }

        return $this->render('process_validation/_reserve.html.twig', [
            'process' => $process,
            'objet' => $objet,
            'processData' => $processData ?? null,
            'type' => $type,
            'id' => $id,
            'etape' => $etape,
        ]);
    }

    #[Route('/validation/edit/{type}/{id}', name: 'app_validation_edit')]
    public function edit(
        DpeParcoursRepository  $dpeParcoursRepository,
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        Request             $request,
        string              $type,
        int                 $id
    ) {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $process = $this->validationProcess->getEtape($data['etat']);
            //mise à jour du workflow
            switch ($type) {
                case 'formation':
                    $objet = $formationRepository->find($id);

                    if ($objet === null) {
                        return JsonReponse::error('Formation non trouvée');
                    }

                    if ($objet->isHasParcours() === false) {
                        //formation sans parcours
                        $dpe = $dpeParcoursRepository->findOneBy(['parcours' => $objet->getParcours()->first(), 'campagneCollecte' => $this->getDpe()]);
                        if ($dpe === null) {
                            return JsonReponse::error('Formation non trouvée');
                        }
                        $dpe->setEtatValidation([$process['transition'] => 1]);
                        $histoEvent = new HistoriqueFormationEvent($objet, $this->getUser(), $data['etat'], 'valide', $request);
                        $this->eventDispatcher->dispatch($histoEvent, HistoriqueFormationEvent::ADD_HISTORIQUE_FORMATION);
                        $this->entityManager->flush();
                        return JsonReponse::success('Validation modifiée');
                    }

                    break;
                case 'parcours':
                    $objet = $parcoursRepository->find($id);
                    $dpe = $dpeParcoursRepository->findOneBy(['parcours' => $objet, 'campagneCollecte' => $this->getDpe()]);
                    if ($objet === null) {
                        return JsonReponse::error('Parcours non trouvé');
                    }

                    $dpe->setEtatValidation([$process['transition'] => 1]);
                    //mettre à jour l'historique
                    $histoEvent = new HistoriqueParcoursEvent($objet, $this->getUser(), $data['etat'], 'valide', $request);
                    $this->eventDispatcher->dispatch($histoEvent, HistoriqueParcoursEvent::ADD_HISTORIQUE_PARCOURS);
                    $this->entityManager->flush();
                    return JsonReponse::success('Validation modifiée');
                    break;
            }

            return JsonReponse::error('Erreur lors de la modification de l\'état de validation');
        }

        return $this->render('process_validation/_edit.html.twig', [
            'etats' => $this->validationProcess->getProcess(),
            'type' => $type,
            'id' => $id,
        ]);
    }

    #[Route('/validation/valide-lot/{etape}', name: 'app_validation_valide_lot')]
    public function valideLot(
        DpeParcoursRepository $dpeParcoursRepository,
        ParcoursRepository $parcoursRepository,
        string              $etape,
        Request             $request
    ): Response {
        if ($request->isMethod('POST')) {
            $sParcours = $request->request->get('parcours');
        } else {
            $sParcours = $request->query->get('parcours');
        }
        $allParcours = explode(',', $sParcours);

        $process = $this->validationProcess->getEtape($etape);
        $laisserPasser = false;
        $tParcours = [];
        foreach ($allParcours as $id) {
            $objet = $parcoursRepository->find($id);

            if ($objet === null) {
                return JsonReponse::error('Parcours non trouvé');
            }
            $dpe = $dpeParcoursRepository->findLastDpeForParcours($objet);
            if ($dpe === null) {
                return JsonReponse::error('Parcours non trouvé');
            }
            $tParcours[] = $dpe;
            //            if ($etape === 'cfvu') {
            //                $histo = $objet->getHistoriqueFormations();
            //                foreach ($histo as $h) {
            //                    if ($h->getEtape() === 'conseil') {
            //                        if ($h->getEtat() === 'laisserPasser' && ($laisserPasser === false || $laisserPasser->getCreated() < $h->getCreated())) {
            //                            $laisserPasser = $h;
            //                        } elseif ($h->getEtat() === 'valide' && $laisserPasser->getCreated() < $h->getCreated()) {
            //                            $laisserPasser = false;
            //                        }
            //                    }
            //                }
            //            }

            $processData = $this->parcoursProcess->etatParcours($dpe, $process);

            if ($request->isMethod('POST')) {
                $this->parcoursProcess->valideParcours($dpe, $this->getUser(), $process, $etape, $request);
            }
        }

        if ($request->isMethod('POST')) {
            $this->toast('success', 'Parcours validés');
            return $this->redirectToRoute('app_validation_index');
        }


        return $this->render('process_validation/_valide_lot.html.twig', [
            'formations' => $tParcours,
            'sParcours' => $sParcours,
            'process' => $process,
            'type' => 'lot',
            'id' => $id,
            'etape' => $etape,
            'processData' => $processData ?? null,
            'laisserPasser' => $laisserPasser,
        ]);
    }

    #[Route('/validation/refuse-lot/{etape}', name: 'app_validation_refuse_lot')]
    public function refuseLot(
        DpeParcoursRepository $dpeParcoursRepository,
        ParcoursRepository $parcoursRepository,
        string              $etape,
        Request             $request
    ): Response {
        if ($request->isMethod('POST')) {
            $sParcours = $request->request->get('parcours');
        } else {
            $sParcours = $request->query->get('parcours');
        }
        $allParcours = explode(',', $sParcours);

        $process = $this->validationProcess->getEtape($etape);
        $tParcours = [];
        foreach ($allParcours as $id) {
            $objet = $parcoursRepository->find($id);

            if ($objet === null) {
                return JsonReponse::error('Formation non trouvée');
            }
            $dpe = $dpeParcoursRepository->findLastDpeForParcours($objet);
            if ($dpe === null) {
                return JsonReponse::error('Parcours non trouvé');
            }
            $tParcours[] = $dpe;
            $processData = $this->parcoursProcess->etatParcours($dpe, $process);

            if ($request->isMethod('POST')) {
                $this->parcoursProcess->refuseParcours($dpe, $this->getUser(), $process, $etape, $request);
            }
        }

        if ($request->isMethod('POST')) {
            $this->toast('success', 'Parcours refusés');
            return $this->redirectToRoute('app_validation_index');
        }

        return $this->render('process_validation/_refuse_lot.html.twig', [
            'formations' => $tParcours,
            'sParcours' => $sParcours,
            'process' => $process,
            'type' => 'lot',
            'id' => $id,
            'etape' => $etape,
            'objet' => $objet,
            'processData' => $processData ?? null,
        ]);
    }

    #[Route('/validation/reserve-lot/{etape}', name: 'app_validation_reserve_lot')]
    public function reserveLot(
        DpeParcoursRepository $dpeParcoursRepository,
        ParcoursRepository $parcoursRepository,
        string              $etape,
        Request             $request
    ): Response {
        if ($request->isMethod('POST')) {
            $sParcours = $request->request->get('parcours');
        } else {
            $sParcours = $request->query->get('formations');
        }
        $allParcours = explode(',', $sParcours);

        $process = $this->validationProcess->getEtape($etape);
        $tParcours = [];
        foreach ($allParcours as $id) {
            $objet = $parcoursRepository->find($id);

            if ($objet === null) {
                return JsonReponse::error('Parcours non trouvé');
            }
            $dpe = $dpeParcoursRepository->findLastDpeForParcours($objet);
            if ($dpe === null) {
                return JsonReponse::error('Parcours non trouvé');
            }
            $tParcours[] = $objet;
            $processData = $this->parcoursProcess->etatParcours($dpe, $process);

            if ($request->isMethod('POST')) {
                $this->parcoursProcess->reserveParcours($dpe, $this->getUser(), $process, $etape, $request);
            }
        }

        if ($request->isMethod('POST')) {
            $this->toast('success', 'Formations marquées avec des réserves');
            return $this->redirectToRoute('app_validation_index');
        }

        return $this->render('process_validation/_reserve_lot.html.twig', [
            'formations' => $tParcours,
            'sParcours' => $sParcours,
            'process' => $process,
            'objet' => $objet,
            'processData' => $processData ?? null,
            'type' => 'lot',
            'id' => $id,
            'etape' => $etape,
        ]);
    }

    #[Route('/demande/reouverture', name: 'app_validation_demande_reouverture')]
    public function demandeReouverture(
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        VersioningParcours $versioningParcours,
        Request             $request
    ): Response {
        $type = $request->query->get('type');
        $id = $request->query->get('id');

        $formation = null;
        $parcours = null;

        switch ($type) {
            case 'formation':
                $formation = $formationRepository->find($id);
                $parcours = null;
                $typeDpe = 'F';

                if ($formation === null) {
                    return JsonReponse::error('Formation non trouvée');
                }
                break;
            case 'parcours':
                $parcours = $parcoursRepository->find($id);

                if ($parcours === null) {
                    return JsonReponse::error('Parcours non trouvé');
                }
                $formation = $parcours->getFormation();
                $typeDpe = 'P';
                break;
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            if ($this->isGranted('ROLE_SES')) {
                $dpe = GetDpeParcours::getFromParcours($parcours);
                //réouverture directe sans sauvegarde ou avec sauvegarde selon le choix
                if ($data['demandeReouverture'] === 'MODIFICATION_SANS_CFVU') {

                    $dpe->setEtatValidation(['soumis_central' => 1]); //un état de processus différent pour connaitre le branchement ensuite
                    $dpe->setEtatReconduction(TypeModificationDpeEnum::MODIFICATION_TEXTE);
                    $histoEvent = new HistoriqueParcoursEvent($parcours, $this->getUser(), 'soumis_central', 'valide', $request);
                    $this->eventDispatcher->dispatch($histoEvent, HistoriqueParcoursEvent::ADD_HISTORIQUE_PARCOURS);
                    $this->entityManager->flush();
                } elseif ($data['demandeReouverture'] === 'MODIFICATION_AVEC_CFVU') {
                    $dpe->setEtatValidation(['soumis_central' => 1]);
                    $dpe->setEtatReconduction(TypeModificationDpeEnum::MODIFICATION_MCCC);
                    // todo: créer un nouveau DPE?
                    // faire la copie de version
                    $now = new DateTimeImmutable('now');
                    $versioningParcours->saveVersionOfParcours($parcours, $now, true, true);

                    $this->entityManager->flush();
                }
                //                $this->addFlash('success', 'DPE ouvert');
                //                return $this->redirectToRoute('app_parcours_edit', ['id' => $parcours->getId()]);
                return JsonReponse::success('DPE ouvert');
            }

            $demande = new DpeDemande();
            $demande->setFormation($formation);
            $demande->setParcours($parcours);
            $demande->setNiveauDemande($typeDpe);
            $demande->setArgumentaireDemande($data['argumentaire_demande_reouverture']);
            $demande->setEtatDemande('attente');
            $demande->setNiveauModification(TypeModificationDpeEnum::tryFrom($data['demandeReouverture']));
            //$demande->setUser($this->getUser());
            $this->entityManager->persist($demande);
            $this->entityManager->flush();

            //mail au SES
            $dpeDemandeEvent = new DpeDemandeEvent($demande, $this->getUser());
            $this->eventDispatcher->dispatch($dpeDemandeEvent, DpeDemandeEvent::DPE_DEMANDE_CREATED);

            return JsonReponse::success('Demande de réouverture enregistrée');
        }

        if ($this->isGranted('ROLE_SES')) {
            return $this->render('process_validation/_demande_reouverture_ses.html.twig', [
                'type' => $type,
                'id' => $id,
            ]);
        }

        return $this->render('process_validation/_demande_reouverture.html.twig', [
            'type' => $type,
            'id' => $id,
        ]);

    }

    #[Route('/demande/reouverture/cloture', name: 'app_validation_demande_reouverture_cloture')]
    public function demandeReouvertureCloture(
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        Request             $request
    ): Response {
        $type = $request->query->get('type');
        $id = $request->query->get('id');

        $formation = null;
        $parcours = null;

        switch ($type) {
            case 'formation':
                $formation = $formationRepository->find($id);
                $parcours = null;
                $typeDpe = 'F';

                if ($formation === null) {
                    return JsonReponse::error('Formation non trouvée');
                }
                break;
            case 'parcours':
                $parcours = $parcoursRepository->find($id);

                if ($parcours === null) {
                    return JsonReponse::error('Parcours non trouvé');
                }
                $formation = $parcours->getFormation();
                $typeDpe = 'P';
                break;
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            if ($this->isGranted('ROLE_SES')) {
                //réouverture directe sans sauvegarde ou avec sauvegarde selon le choix
                if ($data['demandeReouverture'] === 'VALIDE_MODIFICATION_SANS_CFVU') {
                    $dpe = GetDpeParcours::getFromParcours($parcours);
                    $dpe->setEtatValidation(['valide_a_publier' => 1]); //un état de processus différent pour connaitre le branchement ensuite
                    $parcours->getDpeParcours()->first()->setEtatReconduction(TypeModificationDpeEnum::OUVERT);

                    $histoEvent = new HistoriqueParcoursEvent($parcours, $this->getUser(), 'cloture_ses_ss_cfvu', 'valide', $request);
                    $this->eventDispatcher->dispatch($histoEvent, HistoriqueParcoursEvent::ADD_HISTORIQUE_PARCOURS);

                    $this->entityManager->flush();
                } elseif ($data['demandeReouverture'] === 'MODIFICATION_AVEC_CFVU') {
                    $parcours->getDpeParcours()?->first()->setEtatValidation(['central' => 1]); //un état de processus différent pour connaitre le branchement ensuite
                    $formation->getDpe()?->getDpeParcours()->first()->setEtatValidation(['soumis_central' => 1]);
                    $this->entityManager->flush();
                }

                //                $this->addFlash('success', 'DPE cloturé.');
                // return $this->redirectToRoute('app_parcours_show', ['id' => $parcours->getId()]);
                return JsonReponse::success('DPE cloturé');
            }


            return JsonReponse::success('Demande de réouverture enregistrée');
        }

        if ($this->isGranted('ROLE_SES')) {
            return $this->render('process_validation/_demande_reouverture_cloture_ses.html.twig', [
                'type' => $type,
                'id' => $id,
            ]);
        }

        return $this->render('process_validation/_demande_reouverture.html.twig', [
            'type' => $type,
            'id' => $id,
        ]);

    }
}
