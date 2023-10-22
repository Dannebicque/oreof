<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Entity\Composante;
use App\Message\Export;
use App\Repository\AnneeUniversitaireRepository;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Utils\Tools;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends BaseController
{
    public const TYPES_DOCUMENT = [
        "xlsx-mccc" => 'MCCC format Excel (xslx)',
        "pdf-mccc" => 'MCCC format PDF',
        "xlsx-light_mccc" => 'MCCC simplifiés format Excel (xslx)',
        "pdf-light_mccc" => 'MCCC simplifiés format PDF',
        "pdf-fiches" => 'Fiches descriptions format PDF'
    ];


    #[Route('/export', name: 'app_export_index')]
    public function index(
        AnneeUniversitaireRepository $anneeUniversitaireRepository,
        ComposanteRepository         $composanteRepository,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_SES');

        return $this->render('export/index.html.twig', [
            'annees' => $anneeUniversitaireRepository->findAll(),
            'composantes' => $composanteRepository->findAll(),
            'ses' => true,
            'types_document' => self::TYPES_DOCUMENT,
        ]);
    }

    #[Route('/export/composante/{composante}', name: 'app_export_composante_index')]
    public function composante(
        AnneeUniversitaireRepository $anneeUniversitaireRepository,
        Composante                   $composante,
    ): Response {
        return $this->render('export/index.html.twig', [
            'annees' => $anneeUniversitaireRepository->findAll(),
            'composante' => $composante,
        ]);
    }

    #[Route('/export/liste', name: 'app_export_liste')]
    public function liste(
        ComposanteRepository $composanteRepository,
        FormationRepository  $formationRepository,
        Request              $request
    ): Response {
        $composante = $composanteRepository->find($request->query->get('composante'));

        if (!$composante) {
            throw $this->createNotFoundException('La composante n\'existe pas');
        }

        $formations = $formationRepository->findByComposante($composante, $this->getAnneeUniversitaire());

        return $this->render('export/_liste.html.twig', [
            'formations' => $formations
        ]);
    }

    #[Route('/export/valide', name: 'app_export_valide')]
    public function valide(
        MessageBusInterface          $messageBus,
        AnneeUniversitaireRepository $anneeUniversitaireRepository,
        ComposanteRepository         $composanteRepository,
        Request                      $request,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_SES');//todo: ou DPE

        $composante = $composanteRepository->find($request->request->get('composante'));

        if (!$composante) {
            throw $this->createNotFoundException('La composante n\'existe pas');
        }

        $annee = $anneeUniversitaireRepository->find($request->request->get('annee_universitaire'));
        if (!$annee) {
            throw $this->createNotFoundException('L\'année universitaire n\'existe pas');
        }

        $messageBus->dispatch(new Export(
            $this->getUser()?->getId(),
            $request->request->get('type_document'),
            $request->request->all()['liste'],
            $annee->getId(),
            Tools::convertDate($request->request->get('date', null))
        ));

        return JsonReponse::success('Les documents sont en cours de génération, vous recevrez un mail lorsque les documents seront prêts');
    }
}
