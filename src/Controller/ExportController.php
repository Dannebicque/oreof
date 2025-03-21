<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Entity\Composante;
use App\Message\Export;
use App\Repository\CampagneCollecteRepository;
use App\Repository\ComposanteRepository;
use App\Repository\DpeParcoursRepository;
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
        "xlsx-fiabilisation" => 'Export Fiabilisation format excel (xslx)',
        "pdf-mccc" => 'MCCC format PDF',
        "xlsx-light_mccc" => 'MCCC simplifiées format Excel (xslx)',
        "xlsx-version_mccc" => 'MCCC versionnées format Excel (xslx)',
        "xlsx-responsable_compo" => 'Tableau des responsables / compo (xslx)',
        "pdf-light_mccc" => 'MCCC simplifiées format PDF',
        "pdf-fiches" => 'Fiches descriptions format PDF'
    ];

    public const TYPES_DOCUMENT_GLOBAL = [
        "xlsx-carif" => 'Tableau CARIF (xslx)',
        "xlsx-ec" => 'Fiches EC/Type (xslx)',
        "xlsx-listefiche" => 'Fiches matières (xslx)',
        "xlsx-seip" => 'Tableau SEIP (xslx)',
        "xlsx-regime" => 'Tableau Régimes Inscriptions (xslx)',
        "xlsx-responsable" => 'Tableau des responsables (xslx)',
        "xlsx-cfvu" => 'Tableau Synthèse CFVU (xslx)',
    ];


    #[Route('/export', name: 'app_export_index')]
    public function index(
        ComposanteRepository       $composanteRepository,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_SES');

        return $this->render('export/index.html.twig', [
            'composantes' => $composanteRepository->findAll(),
            'ses' => true,
            'isCfvu' => false,
            'types_document' => self::TYPES_DOCUMENT,
            'types_document_global' => self::TYPES_DOCUMENT_GLOBAL,
        ]);
    }

    #[Route('/export/cfvu', name: 'app_export_cfvu')]
    public function exportCfvu(
        ComposanteRepository       $composanteRepository,
    ): Response {
        $autorise = $this->isGranted('CAN_ETABLISSEMENT_CONSEILLER_ALL', $this->getUser()) or $this->isGranted('CAN_ETABLISSEMENT_SHOW_ALL', $this->getUser());

        if (!$autorise) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('export/index.html.twig', [
            'composantes' => $composanteRepository->findAll(),
            'ses' => false,
            'isCfvu' => true,
            'types_document' => self::TYPES_DOCUMENT,
        ]);
    }

    #[Route('/export/consultation', name: 'app_export_show')]
    public function exportShow(
        ComposanteRepository       $composanteRepository,
    ): Response {
        $this->denyAccessUnlessGranted('CAN_ETABLISSEMENT_SHOW_ALL', $this->getUser());

        return $this->render('export/index.html.twig', [
            'composantes' => $composanteRepository->findAll(),
            'ses' => false,
            'isCfvu' => true,
            'types_document' => self::TYPES_DOCUMENT,
            'types_document_global' => self::TYPES_DOCUMENT_GLOBAL,
        ]);
    }

    #[Route('/export/composante/{composante}', name: 'app_export_composante_index')]
    public function composante(
        Composante                 $composante,
    ): Response {
        return $this->render('export/index.html.twig', [
            'composante' => $composante,
            'isCfvu' => false,
            'ses' => false,
            'types_document' => self::TYPES_DOCUMENT,
        ]);
    }

    #[Route('/export/liste', name: 'app_export_liste')]
    public function liste(
        DpeParcoursRepository $dpeParcoursRepository,
        ComposanteRepository $composanteRepository,
        Request              $request
    ): Response {
        $composante = $composanteRepository->find($request->query->get('composante', null));

        if (!$composante) {
            throw $this->createNotFoundException('La composante n\'existe pas');
        }

        if ($this->isGranted('ROLE_SES') ||
            $this->isGranted('CAN_ETABLISSEMENT_SHOW_ALL', $this->getUser())) {
            $dpes = $dpeParcoursRepository->findParcoursByComposante($this->getCampagneCollecte(), $composante);
        } elseif ($this->isGranted('CAN_ETABLISSEMENT_CONSEILLER_ALL', $this->getUser())) {
            $dpes = $dpeParcoursRepository->findParcoursByComposanteCfvu($this->getCampagneCollecte(), $composante);
        } elseif ($this->isGranted('CAN_COMPOSANTE_SHOW_MY', $this->getUser())) {
            $dpes = $dpeParcoursRepository->findParcoursByComposante($this->getCampagneCollecte(), $composante);
        } else {
            $dpes = [];
        }

        return $this->render('export/_liste.html.twig', [
            'dpes' => $dpes
        ]);
    }

    #[Route('/export/valide', name: 'app_export_valide')]
    public function valide(
        MessageBusInterface        $messageBus,
        Request                    $request,
    ): Response {
        $messageBus->dispatch(new Export(
            $this->getUser()?->getId(),
            $request->request->get('type_document'),
            $request->request->all()['liste'] ?? [],
            $this->getCampagneCollecte(),
            Tools::convertDate($request->request->get('date', null)),
            $request->request->get('composante', null),
        ));

        return JsonReponse::success('Les documents sont en cours de génération, vous recevrez un mail lorsque les documents seront prêts');
    }
}
