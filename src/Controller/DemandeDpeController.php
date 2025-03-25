<?php

namespace App\Controller;

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
    #[IsGranted('ROLE_SES')]
    public function index(
    ): Response
    {
        return $this->render('demande_dpe/index.html.twig', [
            'type' => 'ses',
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
        } elseif ($type === 'ses') {
            $this->denyAccessUnlessGranted('ROLE_SES');

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

    #[Route('/demande-dpe/{id}', name: 'app_dpe_demande_delete', methods: ['DELETE'])]
    public function delete(
        EntityManagerInterface $entityManager,
        DpeDemande $dpeDemande,
        Request $request,
    ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dpeDemande->getId(), JsonRequest::getValueFromRequest($request, 'csrf'))) {
            $entityManager->remove($dpeDemande);
            $entityManager->flush();
            return JsonReponse::success('La demande a bien été supprimée.');

        }

        return JsonReponse::error('Erreur lors de la suppression de la demande.');
    }

    //si acceptation ajouter à l'historique, mail DPE +RF? RP? + changement état workflow. Gérer workflow avec ou sans SES
}
