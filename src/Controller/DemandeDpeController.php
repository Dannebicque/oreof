<?php

namespace App\Controller;

use App\Classes\Excel\ExcelWriter;
use App\Classes\JsonReponse;
use App\Entity\Composante;
use App\Entity\DpeDemande;
use App\Repository\ComposanteRepository;
use App\Repository\DpeDemandeRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DemandeDpeController extends AbstractController
{
    #[Route('/demande/dpe', name: 'app_demande_dpe')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
    ): Response
    {
        return $this->render('demande_dpe/index.html.twig', [
            'type' => 'ses'
        ]);
    }

    #[Route('/demande/dpe/liste/{type}', name: 'app_dpe_demande_liste')]
    public function liste(
        ComposanteRepository $composanteRepository,
        DpeDemandeRepository $dpeDemandeRepository,
        Request $request,
        string $type = null
    ): Response
    {
        if ($type === 'composante') {
            $composante = $composanteRepository->find($request->query->get('composante'));
            $this->denyAccessUnlessGranted('CAN_COMPOSANTE_SHOW_MY', $composante);

            if ($composante === null) {
                throw $this->createNotFoundException('Composante non trouvée');
            }

            return $this->render('demande_dpe/_liste.html.twig', [
                'demandes' => $dpeDemandeRepository->findByComposante($composante),
            ]);
        }

        if ($type === 'ses') {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');

            return $this->render('demande_dpe/_liste.html.twig', [
                'demandes' => $dpeDemandeRepository->findAll(),
            ]);
        }

        return $this->redirectToRoute('app_demande_dpe');
    }

    #[Route('/demande/dpe/composante/{composante}', name: 'app_demande_dpe_composante')]
    public function dpeComposante(
        Composante $composante,
    ): Response
    {
        $this->denyAccessUnlessGranted('CAN_COMPOSANTE_SHOW_MY', $composante);

        return $this->render('demande_dpe/index.html.twig', [
            'type' => 'composante',
            'composante' => $composante,
        ]);
    }

    #[Route('/demande/dpe/export/{type}', name: 'app_demande_dpe_export')]
    public function dpeExport(
        ExcelWriter          $excelWriter,
        DpeDemandeRepository $dpeDemandeRepository,
        ComposanteRepository $composanteRepository,
        Request $request,
        string               $type,
    ): Response
    {
        if ($type === 'composante') {
            $composante = $composanteRepository->find($request->query->get('composante'));
            if ($composante === null) {
                throw $this->createNotFoundException('Composante non trouvée');
            }

            $this->denyAccessUnlessGranted('CAN_COMPOSANTE_SHOW_MY', $composante);

            $demandes = $dpeDemandeRepository->findByComposante($composante);
        } else {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');

            $demandes = $dpeDemandeRepository->findAll();
        }

        $filename = 'demandes_dpe_' . date('Y-m-d_H-i-s') . '.xlsx';
        $excelWriter->nouveauFichier('Export Demande DPE');
        $excelWriter->setActiveSheetIndex(0);
        $excelWriter->writeCellName('A1', 'Composante');
        $excelWriter->writeCellName('B1', 'Mention');
        $excelWriter->writeCellName('C1', 'Parcours');
        $excelWriter->writeCellName('D1', 'Demande de ?');
        $excelWriter->writeCellName('E1', 'Date demande');
        $excelWriter->writeCellName('F1', 'Date clôture');
        $excelWriter->writeCellName('G1', 'Niveau demande');
        $excelWriter->writeCellName('H1', 'Etat');
        $excelWriter->writeCellName('I1', 'Commentaire');
        $ligne = 2;
        foreach ($demandes as $demande) {
            $parcours = $demande->getParcours();
            $formation = $parcours->getFormation();
            $composante = $formation->getComposantePorteuse();
            $excelWriter->writeCellName('A' . $ligne, $composante->getLibelle());
            $excelWriter->writeCellName('B' . $ligne, $formation->getDisplay());

            if ($demande->getNiveauDemande() === 'F') {
                $excelWriter->writeCellName('C' . $ligne, 'Niveau Mention');
            } else {
                $excelWriter->writeCellName('C' . $ligne, $parcours->getDisplay());
            }

            $excelWriter->writeCellName('D' . $ligne, $demande->getAuteur() ? $demande->getAuteur()->getDisplay() : '');
            $excelWriter->writeCellName('E' . $ligne, $demande->getDateDemande()?->format('d/m/Y'));
            $excelWriter->writeCellName('F' . $ligne, $demande->getDateCloture() ? $demande->getDateCloture()->format('d/m/Y') : '');
            $excelWriter->writeCellName('G' . $ligne, $demande->getNiveauModification() ? $demande->getNiveauModification()->getLibelle() : '');
            $excelWriter->writeCellName('H' . $ligne, $demande->getEtatDemande()?->getLibelle());
            $excelWriter->writeCellName('I' . $ligne, $demande->getArgumentaireDemande());
            $ligne++;
        }

        $excelWriter->getColumnsAutoSize('A', 'I');

        return $excelWriter->genereFichier($filename);
    }
}
