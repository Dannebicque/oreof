<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Entity\Parcours;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\TypeEcRepository;
use App\Repository\UeRepository;
use App\Utils\Tools;
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
    ): Response
    {
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

    #[Route('/parcours/ressources-sae/{parcours}', name: 'app_parcours_ressources_sae_but')]
    public function ressourcesSae(
        FicheMatiereRepository $ficheMatiereRepository,
        Parcours               $parcours
    )
    {
        $fichesMatieres = $ficheMatiereRepository->findByParcours($parcours);
        $tabEcs = [];
        foreach ($fichesMatieres as $ficheMatiere) {
            $sem = $ficheMatiere->getElementConstitutifs()->first()->getUe()->getSemestre();
            if (array_key_exists($sem->getOrdre(), $tabEcs) === false) {
                $tabEcs[$sem->getOrdre()] = [];
            }
            $tabEcs[$sem->getOrdre()][] = $ficheMatiere;

        }
        return $this->render('parcours_ec/ressources_saes.html.twig', [
            'parcours' => $parcours,
            'tabEcs' => $tabEcs,
        ]);
    }

    #[Route('/parcours/ressources-sae/{parcours}/coeff', name: 'app_parcours_ressources_sae_but_coeff')]
    public function ressourcesSaeCoeff(
        Parcours               $parcours
    )
    {
        $tabEcs = [];
        $tabEcUes = [];
        $tabUes = [];
        foreach ($parcours->getSemestreParcours() as $semParc)
        {
            $tabEcs[$semParc->getOrdre()] = [];
            foreach ($semParc->getSemestre()->getUes() as $ue)
            {
                $tabEcUes[$semParc->getOrdre()][$ue->getId()] = [];
                $tabUes[$semParc->getOrdre()][$ue->getId()] = $ue;
                foreach ($ue->getElementConstitutifs() as $ec)
                {
                    $tabEcUes[$semParc->getOrdre()][$ue->getId()][$ec->getFicheMatiere()->getSigle()] = $ec;
                    $tabEcs[$semParc->getOrdre()][$ec->getFicheMatiere()->getSigle()] = $ec;
                }
            }
            ksort($tabEcs[$semParc->getOrdre()]);
        }

        return $this->render('parcours_ec/ressources_saes_coeff.html.twig', [
            'parcours' => $parcours,
            'tabEcs' => $tabEcs,
            'tabEcUes' => $tabEcUes,
            'tabUes' => $tabUes,
        ]);
    }

    #[Route('/parcours/ec/{parcours}/update', name: 'app_parcours_ec_update')]
    public function updateParcoursEc(
        UeRepository                 $ueRepository,
        ElementConstitutifRepository $ecRepository,
        TypeEcRepository             $typeEcRepository,
        Request                      $request,
        Parcours                     $parcours
    ): Response
    {
        $field = $request->request->get('field');


        switch ($field) {
            case 'typeEc':
                $ec = $ecRepository->find($request->request->get('ec'));

                if ($ec === null) {
                    return JsonReponse::error('EC introuvable');
                }
                $typeEc = $typeEcRepository->find($request->request->get('value'));
                if ($typeEc === null) {
                    return JsonReponse::error('Type EC introuvable');
                }
                $ec->setTypeEc($typeEc);
                $ecRepository->save($ec, true);
                return JsonReponse::success('Type EC mis à jour');
            case 'ectsUE':
                $ue = $ueRepository->find($request->request->get('ue'));

                if ($ue === null) {
                    return JsonReponse::error('UE introuvable');
                }

                $ue->setEcts(Tools::convertToFloat($request->request->get('value')));
                $ueRepository->save($ue, true);
                return JsonReponse::success('ECTS de l\'UE mis à jour');
            case 'coeffEc':
                $ec = $ecRepository->find($request->request->get('ec'));

                if ($ec === null) {
                    return JsonReponse::error('EC introuvable');
                }

                $ec->setEcts(Tools::convertToFloat($request->request->get('value')));
                $ecRepository->save($ec, true);
                return JsonReponse::success('ECTS de l\'EC mis à jour');
        }
    }
}
