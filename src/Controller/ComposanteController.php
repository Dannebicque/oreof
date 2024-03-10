<?php

namespace App\Controller;

use App\Entity\Composante;
use App\Entity\DpeParcours;
use App\Enums\TypeModificationDpeEnum;
use App\Repository\DpeParcoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ComposanteController extends BaseController
{
    #[Route('/composante/{composante<\d+>}', name: 'app_composante')]
    public function index(Composante $composante): Response
    {
        return $this->render('composante/index.html.twig', [
            'composante' => $composante,
        ]);
    }

    #[Route('/composante/campagne-collecte/{composante}', name: 'app_composante_campagne_collecte')]
    public function campagneCollecte(
        DpeParcoursRepository $dpeParcoursRepository,
        Composante $composante): Response
    {
        $parcours = $dpeParcoursRepository->findByComposanteAndCampagne($composante, $this->getDpe());

        $tFormations = [];

        foreach ($parcours as $p) {
            $tFormations[$p->getFormation()?->getId()]['formation'] = $p->getFormation();
            $tFormations[$p->getFormation()?->getId()]['parcours'][] = $p;
        }


        return $this->render('composante/campagne_collecte.html.twig', [
            'composante' => $composante,
            'formations' => $tFormations,
            'campagne' => $this->getDpe()->isDefaut() && $this->getDpe()->isMailDpeEnvoye(),
            'etats' => TypeModificationDpeEnum::cases()
        ]);
    }
}
