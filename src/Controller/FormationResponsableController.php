<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\DTO\ChangeRf;
use App\Entity\Formation;
use App\Form\ChangeRfFormationType;
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
            $newRf->setCommentaire($commentaire);
            $newRf->setDateDemande(new \DateTime());
            $newRf->setAncienResponsable($formation->getResponsableMention());

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
}
