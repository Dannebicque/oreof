<?php

namespace App\Controller;

use App\Entity\Parcours;
use App\Repository\ElementConstitutifRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParcoursEcController extends AbstractController
{
    #[Route('/parcours/ec/{parcours}', name: 'app_parcours_ec')]
    public function index(
        ElementConstitutifRepository $ecRepository,
        Parcours $parcours): Response
    {
        $ecs = $ecRepository->findByParcours($parcours);
        $tabEcs = [];

        foreach ($parcours->getSemestreParcours() as $semestreParcour) {
            $tabEcs[$semestreParcour->getOrdre()] = [];
            foreach ($semestreParcour->getSemestre()->getUes() as $ue) {
                $tabEcs[$semestreParcour->getOrdre()][$ue->getId()] = [];
                foreach ($ue->getElementConstitutifs() as $ec) {
                        $tabEcs[$semestreParcour->getOrdre()][$ue->getId()][] = $ec;
                }
            }
        }


        return $this->render('parcours_ec/index.html.twig', [
            'parcours' => $parcours,
            'tabEcs' => $tabEcs,
        ]);
    }
}
