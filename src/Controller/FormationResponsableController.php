<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Classes\MyGotenbergPdf;
use App\DTO\ChangeRf;
use App\Entity\Formation;
use App\Enums\EtatDemandeChangeRfEnum;
use App\Enums\TypeRfEnum;
use App\Form\ChangeRfFormationType;
use App\Repository\ChangeRfRepository;
use App\Repository\ComposanteRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormationResponsableController extends BaseController
{
    public function __construct(
        private EntityManagerInterface $entityManager
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
        foreach ($composantes as $composante)
        {
            $tDemandes[$composante->getId()] = [];
        }

        foreach ($demandes as $demande)
        {
            $tDemandes[$demande->getFormation()?->getComposantePorteuse()?->getId()][$demande->getFormation()?->getId()]['formation'] = $demande->getFormation();
            $tDemandes[$demande->getFormation()?->getComposantePorteuse()?->getId()][$demande->getFormation()?->getId()]['demandes'][] = $demande;
        }



        return $myGotenbergPdf->render('pdf/formation_responsable_liste.html.twig', [
            'titre' => 'Liste des demande de changement de responsable de formation',
            'demandes' => $tDemandes,
            'composantes' => $composantes
        ], 'synthese_changement_rf_'.(new DateTime())->format('d-m-Y_H-i-s'));
    }
}
