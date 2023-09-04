<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Entity\Parcours;
use App\Repository\ElementConstitutifRepository;
use App\Repository\TypeEcRepository;
use Hoa\Visitor\Element;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParcoursEcController extends AbstractController
{
    #[Route('/parcours/ec/{parcours}', name: 'app_parcours_ec')]
    public function index(
        TypeEcRepository             $typeEcRepository,
        ElementConstitutifRepository $ecRepository,
        Parcours                     $parcours
    ): Response {
        $ecs = $ecRepository->findByParcours($parcours);
        $tabEcs = [];

        foreach ($parcours->getSemestreParcours() as $semestreParcour) {
            $tabEcs[$semestreParcour->getOrdre()] = [];

            if ($semestreParcour->getSemestre()->getSemestreRaccroche() !== null) {
                $semestre = $semestreParcour->getSemestre()->getSemestreRaccroche();
            } else {
                $semestre = $semestreParcour;
            }

            foreach ($semestre->getSemestre()->getUes() as $ue) {
                $tabEcs[$semestreParcour->getOrdre()][$ue->getId()] = [];
                foreach ($ue->getElementConstitutifs() as $ec) {
                    $tabEcs[$semestreParcour->getOrdre()][$ue->getId()][] = $ec;
                }
            }
        }


        return $this->render('parcours_ec/index.html.twig', [
            'parcours' => $parcours,
            'tabEcs' => $tabEcs,
            'typesEc' => $typeEcRepository->findByTypeDiplome($parcours->getFormation()->getTypeDiplome())
        ]);
    }

    #[Route('/parcours/ec/{parcours}/update', name: 'app_parcours_ec_update')]
    public function updateParcoursEc(
        ElementConstitutifRepository $ecRepository,
        TypeEcRepository             $typeEcRepository,
        Request                      $request,
        Parcours                     $parcours
    ): Response {
        $field = $request->request->get('field');
        $ec = $ecRepository->find($request->request->get('ec'));

        if ($ec === null) {
            return JsonReponse::error('EC introuvable');
        }

        switch ($field) {
            case 'typeEc':
                $typeEc = $typeEcRepository->find($request->request->get('value'));
                if ($typeEc === null) {
                    return JsonReponse::error('Type EC introuvable');
                }
                $ec->setTypeEc($typeEc);
                $ecRepository->save($ec, true);
                return JsonReponse::success('Type EC mis à jour');
        }
    }
}
