<?php

namespace App\Controller;

use App\Classes\Codification\CodificationFormation;
use App\Entity\Formation;
use App\Repository\ParcoursRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CodificationController extends AbstractController
{
    #[Route('/codification/{formation}', name: 'app_codification_index')]
    public function index(
        Formation $formation): Response
    {
        return $this->render('codification/index.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/codification/parcours/{formation}', name: 'app_codification_wizard')]
    public function parcoursWizard(
        Request $request,
        ParcoursRepository $parcoursRepository,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Formation $formation): Response
    {
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
            'typeD' => $typeD
        ]);
    }

    #[Route('/codification/genere/{formation}', name: 'app_codification')]
    public function genere(
        CodificationFormation $codificationFormation,
        Formation $formation): Response
    {
        $codificationFormation->setCodificationFormation($formation);

        return $this->redirectToRoute('app_formation_show', ['slug' => $formation->getSlug()]);
    }
}
