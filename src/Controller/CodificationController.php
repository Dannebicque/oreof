<?php

namespace App\Controller;

use App\Classes\Apogee\ExportApogee;
use App\Classes\Codification\CodificationFormation;
use App\Classes\Export\Export;
use App\Classes\Export\ExportCodification;
use App\Entity\Formation;
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

    #[Route('/codification/liste/type_diplome', name: 'app_codification_liste_type_diplome')]
    public function listeTypeDiplome(
        Request               $request,
        TypeDiplomeRepository $typeDiplomeRepository,
        FormationRepository   $formationRepository,
    ): Response {
        $typeDiplome = $typeDiplomeRepository->find($request->query->get('step'));

        if ($typeDiplome === null) {
            throw new \Exception('Type de diplôme non trouvé');
        }

        if ($this->isGranted('ROLE_ADMIN') ||
            $this->isGranted('ROLE_SES') ||
            $this->isGranted('CAN_COMPOSANTE_SHOW_ALL', $this->getUser()) ||
            $this->isGranted('CAN_COMPOSANTE_SCOLARITE_ALL', $this->getUser()) ||
            $this->isGranted('CAN_ETABLISSEMENT_SHOW_ALL', $this->getUser()) ||
            $this->isGranted('CAN_ETABLISSEMENT_SCOLARITE_ALL', $this->getUser()) ||
            $this->isGranted('CAN_FORMATION_SHOW_ALL', $this->getUser())) {
            $formations = $formationRepository->findByAnneeUniversitaireAndTypeDiplome($this->getDpe(), $typeDiplome);
        } else {
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
        }

        $tFormations = [];
        foreach ($formations as $formation) {
            $tFormations[$formation->getId()] = $formation;
        }

        return $this->render('codification/_liste.html.twig', [
            'formations' => $tFormations,
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
        } else {
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
        }

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
        ExportApogee        $exportApogee,
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
        $exportApogee->genereExportApogee($parcours);
        $apogee = $exportApogee->tabsElp;

        return $this->render('codification/_parcours.html.twig', [
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'dto' => $tParcours,
            'parcours' => $parcours,
            'typeD' => $typeD,
            'apogee' => $apogee,
        ]);
    }

    #[Route('/codification/genere/all', name: 'app_codification_genere_all')]
    public function genereAll(
        FormationRepository   $formationRepository,
        CodificationFormation $codificationFormation,
    ): Response {
        $formations = $formationRepository->findBy(['anneeUniversitaire' => $this->getDpe()]);
        foreach ($formations as $formation) {
            $codificationFormation->setCodificationFormation($formation);
        }

        return $this->redirectToRoute('app_codification_liste');
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
