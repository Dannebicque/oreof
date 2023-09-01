<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Entity\Parcours;
use App\Repository\CompetenceRepository;
use App\Repository\ElementConstitutifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParcoursBccController extends AbstractController
{
    #[Route('/parcours/bcc/{parcours}', name: 'app_parcours_bcc')]
    public function index(Parcours $parcours): Response
    {
        return $this->render('parcours_bcc/index.html.twig', [
            'parcours' => $parcours,
            'editable' => true //todo: déterminer selon l'état du process ...
        ]);
    }

    #[Route('/parcours/bcc/{parcours}/update-competence', name: 'app_parcours_bcc_update_competence', methods: ['POST'])]
    public function updateCompetence(
        EntityManagerInterface       $em,
        ElementConstitutifRepository $ecRepository,
        CompetenceRepository         $competenceRepository,
        Request                      $request,
        Parcours                     $parcours
    ): Response {
        $ec = $ecRepository->find($request->request->get('ec'));
        $competence = $competenceRepository->find($request->request->get('competence'));
        if ($competence === null || $ec === null) {
            return JsonReponse::error('EC ou compétence introuvable');
        }

        if ($request->request->get('checked') === 'true') {
            // on ajoute
            if ($ec->getFicheMatiere() === null) {
                return JsonReponse::error('EC sans fiche matière');
            }
            if ($ec->getFicheMatiere()->getParcours() === $parcours) {
                $ec->getFicheMatiere()->addCompetence($competence);
            } else {
                $ec->addCompetence($competence);
            }
        } else {
            if ($ec->getFicheMatiere() === null) {
                return JsonReponse::error('EC sans fiche matière');
            }

            if ($ec->getFicheMatiere()->getParcours() === $parcours) {
                $ec->getFicheMatiere()->removeCompetence($competence);
            } else {
                $ec->removeCompetence($competence);
            }
        }

        $em->flush();
        return JsonReponse::success('Lien compétence/EC mis à jour');
    }
}
