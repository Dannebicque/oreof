<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Entity\Composante;
use App\Message\Export;
use App\Repository\CampagneCollecteRepository;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Utils\Tools;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class ExportController extends BaseController
{
    public const TYPES_DOCUMENT = [
        "xlsx-mccc" => 'MCCC format Excel (xslx)',
        "xlsx-cap" => 'Export CAP format excel (xslx)',
        "pdf-mccc" => 'MCCC format PDF',
        "xlsx-light_mccc" => 'MCCC simplifiés format Excel (xslx)',
        "pdf-light_mccc" => 'MCCC simplifiés format PDF',
        "pdf-fiches" => 'Fiches descriptions format PDF'
    ];

    public const TYPES_DOCUMENT_GLOBAL = [
        "xlsx-carif" => 'Tableau CARIF (xslx)',
        "xlsx-ec" => 'Fiches EC/Type (xslx)',
        "xlsx-seip" => 'Tableau SEIP (xslx)',
        "xlsx-regime" => 'Tableau Régimes Inscriptions (xslx)',
        "xlsx-cfvu" => 'Tableau Synthèse CFVU (xslx)',
    ];


    #[Route('/export', name: 'app_export_index')]
    public function index(
        CampagneCollecteRepository $anneeUniversitaireRepository,
        ComposanteRepository       $composanteRepository,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_SES');

        return $this->render('export/index.html.twig', [
            'annees' => $anneeUniversitaireRepository->findAll(),
            'composantes' => $composanteRepository->findAll(),
            'ses' => true,
            'isCfvu' => false,
            'types_document' => self::TYPES_DOCUMENT,
            'types_document_global' => self::TYPES_DOCUMENT_GLOBAL,
        ]);
    }

    #[Route('/export/cfvu', name: 'app_export_cfvu')]
    public function exportCfvu(
        CampagneCollecteRepository $anneeUniversitaireRepository,
        ComposanteRepository       $composanteRepository,
    ): Response {
        $this->denyAccessUnlessGranted('CAN_ETABLISSEMENT_CONSEILLER_ALL', $this->getUser());

        return $this->render('export/index.html.twig', [
            'annees' => $anneeUniversitaireRepository->findAll(),
            'composantes' => $composanteRepository->findAll(),
            'ses' => false,
            'isCfvu' => true,
            'types_document' => self::TYPES_DOCUMENT,
        ]);
    }

    #[Route('/export/consultation', name: 'app_export_show')]
    public function exportShow(
        CampagneCollecteRepository $anneeUniversitaireRepository,
        ComposanteRepository       $composanteRepository,
    ): Response {
        $this->denyAccessUnlessGranted('CAN_ETABLISSEMENT_SHOW_ALL', $this->getUser());

        return $this->render('export/index.html.twig', [
            'annees' => $anneeUniversitaireRepository->findAll(),
            'composantes' => $composanteRepository->findAll(),
            'ses' => false,
            'isCfvu' => true,
            'types_document' => self::TYPES_DOCUMENT,
            'types_document_global' => self::TYPES_DOCUMENT_GLOBAL,
        ]);
    }

    #[Route('/export/composante/{composante}', name: 'app_export_composante_index')]
    public function composante(
        CampagneCollecteRepository $anneeUniversitaireRepository,
        Composante                 $composante,
    ): Response {
        return $this->render('export/index.html.twig', [
            'annees' => $anneeUniversitaireRepository->findAll(),
            'composante' => $composante,
            'isCfvu' => false,
            'ses' => false,
            'types_document' => self::TYPES_DOCUMENT,
        ]);
    }

    #[Route('/export/liste', name: 'app_export_liste')]
    public function liste(
        ComposanteRepository $composanteRepository,
        FormationRepository  $formationRepository,
        Request              $request
    ): Response {
        $composante = $composanteRepository->find($request->query->get('composante', null));
//todo: gérer l'année du DPE en liste déroualante
        if (!$composante) {
            throw $this->createNotFoundException('La composante n\'existe pas');
        }

        if ($this->isGranted('ROLE_SES') || $this->isGranted('CAN_ETABLISSEMENT_SHOW_ALL', $this->getUser())) {
            $formations = $formationRepository->findByComposante($composante, $this->getDpe());
        } elseif ($this->isGranted('CAN_ETABLISSEMENT_CONSEILLER_ALL', $this->getUser())) {
            $formations = $formationRepository->findByComposanteCfvu($composante, $this->getDpe());
        } else {
            $formations = [];
        }

        return $this->render('export/_liste.html.twig', [
            'formations' => $formations
        ]);
    }

    #[Route('/export/valide', name: 'app_export_valide')]
    public function valide(
        MessageBusInterface        $messageBus,
        CampagneCollecteRepository $anneeUniversitaireRepository,
        Request                    $request,
    ): Response {
        $annee = $anneeUniversitaireRepository->find($request->request->get('annee_universitaire'));
        if (!$annee) {
            throw $this->createNotFoundException('L\'année universitaire n\'existe pas');
        }

        $messageBus->dispatch(new Export(
            $this->getUser()?->getId(),
            $request->request->get('type_document'),
            $request->request->all()['liste'] ?? [],
            $this->getDpe(),
            Tools::convertDate($request->request->get('date', null))
        ));

        return JsonReponse::success('Les documents sont en cours de génération, vous recevrez un mail lorsque les documents seront prêts');
    }
}
