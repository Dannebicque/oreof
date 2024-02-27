<?php

namespace App\Controller;

use App\Classes\Apogee\ExportApogee;
use App\Classes\Codification\CodificationFormation;
use App\Classes\Export\ExportCodification;
use App\Classes\GetFormations;
use App\Entity\Formation;
use App\Entity\TypeDiplome;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use App\Repository\TypeDiplomeRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CodificationController extends BaseController
{
    #[Route('/codification/liste', name: 'app_codification_liste')]
    public function liste(
        TypeDiplomeRepository $typeDiplomeRepository,
    ): Response {
        return $this->render('codification/liste.html.twig', [
            'typeDiplomes' => $typeDiplomeRepository->findAll(),
        ]);
    }

    #[Route('/codification/liste-inter', name: 'app_codification_liste_inter')]
    public function listeInter(
        TypeDiplomeRepository $typeDiplomeRepository,
        Request               $request,
    ): Response {
        $typeDiplome = $typeDiplomeRepository->find($request->query->get('step'));

        if ($typeDiplome === null) {
            throw new \Exception('Type de diplôme non trouvé');
        }

        return $this->render('codification/_liste-inter.html.twig', [
            'typeDiplome' => $typeDiplome,
        ]);
    }

    #[Route('/codification/liste/type_diplome/{typeDiplome}', name: 'app_codification_liste_type_diplome')]
    public function listeTypeDiplome(
        GetFormations         $getFormations,
        Request               $request,
        TypeDiplomeRepository $typeDiplomeRepository,
        TypeDiplome           $typeDiplome
    ): Response {
        if ($typeDiplome === null) {
            throw new \Exception('Type de diplôme non trouvé');
        }

        $filtres = $request->query->all();
        $filtres['typeDiplome'] = $typeDiplome->getId();

        if ($this->isGranted('ROLE_ADMIN') ||
            $this->isGranted('ROLE_SES') ||
            $this->isGranted('CAN_COMPOSANTE_SHOW_ALL', $this->getUser()) ||
            $this->isGranted('CAN_COMPOSANTE_SCOLARITE_ALL', $this->getUser()) ||
            $this->isGranted('CAN_ETABLISSEMENT_SHOW_ALL', $this->getUser()) ||
            $this->isGranted('CAN_ETABLISSEMENT_SCOLARITE_ALL', $this->getUser()) ||
            $this->isGranted('CAN_FORMATION_SHOW_ALL', $this->getUser())) {
            //$formations = $formationRepository->findByDpeAndTypeDiplome($this->getDpe(), $typeDiplome);
            $tFormations = $getFormations->getFormations(
                $this->getUser(),
                $this->getDpe(),
                $filtres,
                false
            );
        } else {
            //filtrer par type de diplôme
//            $formations = [];
//            //gérer le cas ou l'utilisateur dispose des droits pour lire la composante
//            $centres = $this->getUser()?->getUserCentres();
//            foreach ($centres as $centre) {
//                //todo: gérer avec un voter
//                if ($centre->getComposante() !== null && (
//                    in_array('Gestionnaire', $centre->getDroits()) ||
//                    in_array('Invité', $centre->getDroits()) ||
//                    in_array('Directeur', $centre->getDroits())
//                )) {
//                    $formations[] = $formationRepository->findByComposante(
//                        $centre->getComposante(),
//                        $this->getDpe()
//                    );
//                }
//            }
//
//            $formations[] = $formationRepository->findByComposanteDpe(
//                $this->getUser(),
//                $this->getDpe()
//            );
//            $formations[] = $formationRepository->findByResponsableOuCoResponsable(
//                $this->getUser(),
//                $this->getDpe()
//            );
//            $formations[] = $formationRepository->findByResponsableOuCoResponsableParcours(
//                $this->getUser(),
//                $this->getDpe()
//            );
//            $formations = array_merge(...$formations);
        }

        return $this->render('codification/_liste.html.twig', [
            'formations' => $tFormations,
            'typeDiplome' => $typeDiplome,
            'params' => $request->query->all(),
        ]);
    }


    #[Route('/codification/export', name: 'app_codification_export')]
    public function export(
        ExportCodification  $export,
        FormationRepository $formationRepository,
    ): Response {
        if ($this->isGranted('ROLE_ADMIN') ||
            $this->isGranted('ROLE_SES') ||
            $this->isGranted('CAN_COMPOSANTE_SHOW_ALL', $this->getUser()) ||
            $this->isGranted('CAN_COMPOSANTE_SCOLARITE_ALL', $this->getUser()) ||
            $this->isGranted('CAN_ETABLISSEMENT_SHOW_ALL', $this->getUser()) ||
            $this->isGranted('CAN_ETABLISSEMENT_SCOLARITE_ALL', $this->getUser()) ||
            $this->isGranted('CAN_FORMATION_SHOW_ALL', $this->getUser())) {
            $formations = $formationRepository->findBySearch('', $this->getDpe(), []);

            return $export->exportFormations($formations);
        }

        $formations = [];
        //gérer le cas ou l'utilisateur dispose des droits pour lire la composante
        $centres = $this->getUser()?->getUserCentres();
        foreach ($centres as $centre) {
            //todo: gérer avec un voter
            if ($centre->getComposante() !== null && (
                in_array('Gestionnaire', $centre->getDroits()) ||
                in_array('Invité', $centre->getDroits()) ||
                in_array('Directeur', $centre->getDroits())
            )) {
                //todo: il faudrait pouvoir filtrer par ce que contient le rôle et pas juste le nom
                $formations[] = $formationRepository->findByComposante(
                    $centre->getComposante(),
                    $this->getDpe()
                );
            }
        }

        $formations[] = $formationRepository->findByComposanteDpe(
            $this->getUser(),
            $this->getDpe()
        );
        $formations[] = $formationRepository->findByResponsableOuCoResponsable(
            $this->getUser(),
            $this->getDpe()
        );
        $formations[] = $formationRepository->findByResponsableOuCoResponsableParcours(
            $this->getUser(),
            $this->getDpe()
        );
        $formations = array_merge(...$formations);

        $tFormations = [];
        foreach ($formations as $formation) {
            $tFormations[$formation->getId()] = $formation;
        }

        return $this->render('codification/liste.html.twig', [
            'formations' => $tFormations,
        ]);
    }

    #[Route('/codification/{formation}', name: 'app_codification_index')]
    public function index(
        Formation $formation
    ): Response {
        return $this->render('codification/index.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/codification/parcours/{formation}', name: 'app_codification_wizard')]
    public function parcoursWizard(
        Request             $request,
        ParcoursRepository  $parcoursRepository,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Formation           $formation
    ): Response {
        $idParcours = $request->query->get('step');
        $parcours = $parcoursRepository->find($idParcours);

        if ($parcours === null) {
            throw new \Exception('Parcours non trouvé');
        }


        $typeDiplome = $formation->getTypeDiplome();

        if ($typeDiplome === null) {
            throw new \Exception('Type de diplôme non trouvé');
        }

        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());
        $tParcours = $typeD->calculStructureParcours($parcours);

        return $this->render('codification/_parcours.html.twig', [
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'dto' => $tParcours,
            'parcours' => $parcours,
            'typeD' => $typeD,
        ]);
    }

    #[Route('/codification/genere/{formation}', name: 'app_codification')]
    public function genere(
        CodificationFormation $codificationFormation,
        Formation             $formation
    ): Response {
        $codificationFormation->setCodificationFormation($formation);

        return $this->redirectToRoute('app_codification_index', ['formation' => $formation->getId()]);
    }
}
