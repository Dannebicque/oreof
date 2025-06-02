<?php

namespace App\Controller;

use App\Classes\Excel\ExcelWriter;
use App\Classes\JsonReponse;
use App\Classes\ValidationProcess;
use App\Entity\Actualite;
use App\Entity\Composante;
use App\Entity\DpeDemande;
use App\Form\ActualiteType;
use App\Form\DpeDemandeTexteType;
use App\Repository\ActualiteRepository;
use App\Repository\ComposanteRepository;
use App\Repository\DpeDemandeRepository;
use App\Repository\MentionRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DemandeDpeController extends BaseController
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
        ValidationProcess $validationProcess,
        MentionRepository $mentionRepository,
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
                'is_admin' => false,
                'params' => $request->query->all(),
                'demandes' => $dpeDemandeRepository->findByComposanteAndSearch(
                    $composante,
                    $this->getCampagneCollecte(),
                    $request->query->all()),
            ]);
        }

        if ($type === 'ses') {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');

            return $this->render('demande_dpe/_liste.html.twig', [
                'is_admin' => true,
                'listeNiveauModification' => DpeDemande::getListeNiveauModification(),
                'listeEtatValidation' => $validationProcess->getProcessAll(),
                'demandes' => $dpeDemandeRepository->findBySearch(
                    $this->getCampagneCollecte(),
                    $request->query->all()
                ),
                'params' => $request->query->all(),
                'composantes' => $composanteRepository->findPorteuse(),
                'mentions' => $mentionRepository->findBy([], ['libelle' => 'ASC']),
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

    #[Route('/demande/dpe/{id}/edit', name: 'app_demande_dpe_edit', methods: ['GET', 'POST'])]
    public function edit(
        EntityManagerInterface $entityManager,
        Request                $request, DpeDemande $dpeDemande, DpeDemandeRepository $dpeDemandeRepository): Response
    {


        $form = $this->createForm(DpeDemandeTexteType::class, $dpeDemande, [
            'action' => $this->generateUrl('app_demande_dpe_edit', ['id' => $dpeDemande->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (trim($dpeDemande->getArgumentaireDemande()) === '') {
                $this->addFlash('error', 'Le champ "Argumentaire de la demande" ne peut pas être vide.');
                return $this->json(false);
            }
            $entityManager->flush();

            return $this->json(true);
        }

        return $this->render('demande_dpe/edit.html.twig', [
            'dpeDemande' => $dpeDemande,
            'form' => $form->createView(),
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
            if ($demande->getNiveauDemande() === 'F') {
                $formation = $demande->getFormation();
                $composante = $formation?->getComposantePorteuse();
            } else {
                $parcours = $demande->getParcours();
                $formation = $parcours?->getFormation();
                $composante = $formation?->getComposantePorteuse();
            }

            $excelWriter->writeCellName('A' . $ligne, $composante->getLibelle());
            $excelWriter->writeCellName('B' . $ligne, $formation?->getDisplay());

            if ($demande->getNiveauDemande() === 'F') {
                $excelWriter->writeCellName('C' . $ligne, 'Niveau Mention');
            } else {
                $excelWriter->writeCellName('C' . $ligne, $parcours?->getDisplay());
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
