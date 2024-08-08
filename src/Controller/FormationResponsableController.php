<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Classes\MyGotenbergPdf;
use App\Classes\Process\ChangeRfProcess;
use App\Classes\ValidationProcessChangeRf;
use App\DTO\ChangeRf;
use App\Entity\Formation;
use App\Entity\HistoriqueFormation;
use App\Enums\TypeRfEnum;
use App\Events\NotifCentreFormationEvent;
use App\Form\ChangeRfFormationType;
use App\Repository\ChangeRfRepository;
use App\Repository\ComposanteRepository;
use App\Utils\Tools;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;

class FormationResponsableController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WorkflowInterface $changeRfWorkflow,
        private readonly ValidationProcessChangeRf $validationProcess,
        private readonly ChangeRfProcess $changeRfProcess,
    ) {
    }

    #[Route('/formation/change-responsable/ajout/{formation}', name: 'app_formation_change_rf')]
    public function index(
        Formation $formation,
        Request $request
    ): Response {
        $changeRf = new ChangeRf();

        $form = $this->createForm(ChangeRfFormationType::class, $changeRf, [
            'action' => $this->generateUrl(
                'app_formation_change_rf',
                ['formation' => $formation->getId()]
            ),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $datas = $form->getData();
            $user = $datas->getUser();
            $commentaire = $datas->getCommentaire();

            $newRf = new \App\Entity\ChangeRf();
            $newRf->setFormation($formation);
            $newRf->setNouveauResponsable($user);
            $newRf->setTypeRf($datas->getTypeRf());
            $newRf->setCommentaire($commentaire);
            $newRf->setDateDemande(new \DateTime());
            //initialiser le marking du workflow
            $this->changeRfWorkflow->apply($newRf, 'effectuer_demande');

            if ($newRf->getTypeRf() === TypeRfEnum::RF) {
                $newRf->setAncienResponsable($formation->getResponsableMention());
            } else {
                $newRf->setAncienResponsable($formation->getCoResponsable());
            }

            $this->entityManager->persist($newRf);
            $this->entityManager->flush();

            return JsonReponse::success('Le changement de responsable de formation a bien été enregistré.');
        }

        return $this->render('formation_responsable/_index.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,
        ]);
    }

    #[Route('/formation/change-responsable/suppression/{demande}', name: 'app_formation_change_rf_suppression')]
    public function suppressionDemande(
        \App\Entity\ChangeRf $demande,
    ): Response {

        $this->entityManager->remove($demande);
        $this->entityManager->flush();

        return JsonReponse::success('Le changement de responsable de formation a bien été supprimé.');

    }

    #[Route('/formation/change-responsable/liste', name: 'app_formation_responsable_liste')]
    public function listeDemande(
        ChangeRfRepository $changeRfRepository
    ): Response {

        $demandes = $changeRfRepository->findBy([
            'etatDemande' => EtatDemandeChangeRfEnum::EN_ATTENTE
        ], ['dateDemande' => 'DESC']);

        return $this->render('formation_responsable/liste.html.twig', [
            'demandes' => $demandes
        ]);
    }

    #[Route('/formation/change-responsable/export', name: 'app_formation_responsable_liste_export')]
    public function listeDemandeExport(
        MyGotenbergPdf $myGotenbergPdf,
        ChangeRfRepository $changeRfRepository,
        ComposanteRepository $composanteRepository
    ): Response {

        $demandes = $changeRfRepository->findBy([
            'etatDemande' => EtatDemandeChangeRfEnum::EN_ATTENTE
        ], ['dateDemande' => 'DESC']);

        $composantes = $composanteRepository->findAll();
        $tDemandes = [];
        foreach ($composantes as $composante) {
            $tDemandes[$composante->getId()] = [];
        }

        foreach ($demandes as $demande) {
            $tDemandes[$demande->getFormation()?->getComposantePorteuse()?->getId()][$demande->getFormation()?->getId()]['formation'] = $demande->getFormation();
            $tDemandes[$demande->getFormation()?->getComposantePorteuse()?->getId()][$demande->getFormation()?->getId()]['demandes'][] = $demande;
        }



        return $myGotenbergPdf->render('pdf/formation_responsable_liste.html.twig', [
            'titre' => 'Demandes de modification de responsable de formation',
            'demandes' => $tDemandes,
            'composantes' => $composantes,
            'dpe' => $this->getDpe()
        ], 'synthese_changement_rf_'.(new DateTime())->format('d-m-Y_H-i-s'));
    }

    #[Route(
        '/formation/change-responsable/valide-confirm-form/{etape}',
        name: 'app_formation_responsable_valide_confirme'
    )]
    public function valideConfirmeForm(
        EventDispatcherInterface $eventDispatcher,
        KernelInterface $kernel,
        ChangeRfRepository $changeRfRepository,
        string $etape,
        Request $request
    ): Response {
//todo: sans doute deprecated ??
        if ($request->isMethod('POST')) {
            $dir = $kernel->getProjectDir() . '/public/uploads/change_rf/pv/';
            $demandes = explode(',', $request->request->get('demandes'));
            if ($request->files->has('file') && $request->files->get('file') !== null) {
                $file = $request->files->get('file');
                $fileName = md5(uniqid('', true)) . '.' . $file->guessExtension();
                $file->move(
                    $dir,
                    $fileName
                );
                $nomFichier = $fileName;
            }

            foreach ($demandes as $idDemande) {
                $demande = $changeRfRepository->find($idDemande);
                if ($demande !== null) {
                    $dateCfvu = Tools::convertDate($request->request->get('dateCFVU'));
                    $demande->setEtatDemande(EtatDemandeChangeRfEnum::VALIDE);
                    $demande->setDateValidationCfvu($dateCfvu);
                    $demande->setFichierPv($nomFichier);
                    $formation = $demande->getFormation();

                    if ($formation === null) {
                        $this->toast('error', 'Erreur lors de la validation de la demande.');
                        return $this->redirectToRoute('app_validation_index');
                    }

                    if ($demande->getTypeRf() === TypeRfEnum::RF) {
                        $type = 'change_rf';
                        $droits = ['ROLE_RESP_FORMATION'];
                        $formation->setResponsableMention(null);
                    } else {
                        $droits = ['ROLE_CO_RESP_FORMATION'];
                        $type = 'change_rf_co';
                        $formation->setCoResponsable(null);
                    }

                    if ($demande->getNouveauResponsable() !== null) {
                        $eventDispatcher->dispatch(new NotifCentreFormationEvent($demande->getFormation(), $demande->getNouveauResponsable(), $droits), NotifCentreFormationEvent::NOTIF_ADD_CENTRE_FORMATION);

                        if ($demande->getTypeRf() === TypeRfEnum::RF) {
                            $formation->setResponsableMention($demande->getNouveauResponsable());
                        } else {
                            $formation->setCoResponsable($demande->getNouveauResponsable());
                        }
                    }

                    if ($demande->getAncienResponsable() !== null) {
                        $eventDispatcher->dispatch(new NotifCentreFormationEvent($demande->getFormation(), $demande->getAncienResponsable(), $droits), NotifCentreFormationEvent::NOTIF_REMOVE_CENTRE_FORMATION);
                    }

                    $histo = new HistoriqueFormation();
                    $histo->setFormation($formation);
                    $histo->setChangeRf($demande);
                    $histo->setEtape($type);
                    $histo->setUser($this->getUser());
                    $histo->setEtat('valide');
                    $histo->setDate($dateCfvu);
                    $histo->setComplements([
                        'fichier' => $nomFichier
                    ]);
                    $this->entityManager->persist($histo);


                    $this->entityManager->flush();
                }
            }

            $this->toast('success', 'Demandes validées, les droits ont été modifiés.');
            return $this->redirectToRoute('app_validation_index');
        }

        return $this->render('formation_responsable/_valide_confirm_form.html.twig', [
            'etape' => $etape,
            'sDemandes' => $request->query->get('demandes'),
        ]);
    }

    #[Route(
        '/formation/change-responsable/validation-demande/{transition}/{etape}/{demande}',
        name: 'app_validation_change_rf_valider'
    )]
    public function validationChangeRf(
        Request $request,
        string $transition,
        string $etape,
        \App\Entity\ChangeRf $demande,
    ): Response {

        if ($demande === null) {
            return JsonReponse::error('Demande non trouvée');
        }

        $meta = $this->validationProcess->getMetaFromTransition($transition);

        //upload
        $fileName = '';
        if ($request->files->has('file') && $request->files->get('file') !== null) {
            $file = $request->files->get('file');
            $fileName = md5(uniqid('', true)) . '.' . $file->guessExtension();
            $file->move(
                $this->dir,
                $fileName
            );
        }

        $process = $this->validationProcess->getEtape($etape);
        $processData = $this->changeRfProcess->etatChangeRf($demande, $process);

        if ($request->isMethod('POST')) {
            return $this->changeRfProcess->valideChangeRf($demande, $this->getUser(), $transition, $request, $fileName);
        }

        return $this->render('formation_responsable/_valide.html.twig', [
            'demande' => $demande,
            'process' => $process,
            'etape' => $etape,
            'processData' => $processData ?? null,
            'meta' => $meta,
            'transition' => $transition,
        ]);
    }

    #[Route(
        '/formation/change-responsable/validation-demande/{transition}/{etape}/{demande}',
        name: 'app_validation_change_rf_reserver'
    )]
    public function reserverChangeRf(
        Request $request,
        string $transition,
        string $etape,
        \App\Entity\ChangeRf $demande,
    ): Response {

        if ($demande === null) {
            return JsonReponse::error('Demande non trouvée');
        }

        $meta = $this->validationProcess->getMetaFromTransition($transition);

        $process = $this->validationProcess->getEtape($etape);
        $processData = $this->changeRfProcess->etatChangeRf($demande, $process);

        if ($request->isMethod('POST')) {
            return $this->changeRfProcess->reserveChangeRf($demande, $this->getUser(), $transition, $request);
        }

        return $this->render('formation_responsable/_reserve.html.twig', [
            'demande' => $demande,
            'process' => $process,
            'etape' => $etape,
            'processData' => $processData ?? null,
            'meta' => $meta,
            'transition' => $transition,
        ]);
    }
}
