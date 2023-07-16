<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Message\Export;
use App\Repository\AnneeUniversitaireRepository;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Utils\Tools;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends BaseController
{
    #[Route('/export', name: 'app_export_index')]
    public function index(
        AnneeUniversitaireRepository $anneeUniversitaireRepository,
        ComposanteRepository $composanteRepository,

    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SES');

        return $this->render('export/index.html.twig', [
            'annees' => $anneeUniversitaireRepository->findAll(),
            'composantes' => $composanteRepository->findAll(),
        ]);
    }

    #[Route('/export/liste', name: 'app_export_liste')]
    public function liste(
        ComposanteRepository $composanteRepository,
        FormationRepository $formationRepository,
        Request $request
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SES');//todo: ou DPE

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
        MessageBusInterface $messageBus,
        AnneeUniversitaireRepository $anneeUniversitaireRepository,
        ComposanteRepository $composanteRepository,
        FormationRepository $formationRepository,
        Request $request,
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SES');//todo: ou DPE

        $composante = $composanteRepository->find($request->request->get('composante'));

        if (!$composante) {
            throw $this->createNotFoundException('La composante n\'existe pas');
        }

        $annee = $anneeUniversitaireRepository->find($request->request->get('annee_universitaire'));
        if (!$annee) {
            throw $this->createNotFoundException('L\'année universitaire n\'existe pas');
        }

        $formations = [];

        foreach ($request->request->all()['liste'] as $formationId) {
            $formation = $formationRepository->findOneBy(['id' => $formationId, 'anneeUniversitaire' => $annee->getId()]);
            if ($formation && $formation->getComposantePorteuse() === $composante) {
                $formations[] = $formation;
            }
        }

        $messageBus->dispatch(new Export(
            $this->getUser()->getId(),
            $request->request->get('type_document'),
            $formations,
            $annee->getId(),
            Tools::convertDate($request->request->get('date', null))
        ) );

        return JsonReponse::success('Les documents sont en cours de génération, vous recevrez un mail lorsque les documents seront prêts');
    }
}
