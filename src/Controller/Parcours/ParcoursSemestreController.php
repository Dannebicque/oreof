<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Parcours/ParcoursSemestreController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/02/2026 19:03
 */

namespace App\Controller\Parcours;

use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\SemestreParcours;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Petite API pour inclure / toggler des semestres dans un parcours (utilisé par la vue "maquette").
 */
class ParcoursSemestreController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private CsrfTokenManagerInterface $csrf)
    {
    }

    #[Route('/parcours/v2/{parcours}/semestre/{ordre}/include', name: 'parcours_v2_semestre_include', methods: ['POST'])]
    public function include(Request $request, Parcours $parcours, int $ordre): Response
    {
        $token = $request->request->get('_token');
        if (!$this->csrf->isTokenValid(new CsrfToken('include-semestre' . $parcours->getId() . '-' . $ordre, $token))) {
            $this->addFlash('error', 'Token invalide');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
        }

        // chercher Semestre correspondant à l'ordre
        $semestre = $this->em->getRepository(Semestre::class)->findOneBy(['ordre' => $ordre]);
        if (!$semestre) {
            $this->addFlash('error', 'Semestre introuvable');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
        }

        // vérifier s'il existe déjà
        $exists = $this->em->getRepository(SemestreParcours::class)->findOneBy(['parcours' => $parcours, 'ordre' => $ordre]);
        if (!$exists) {
            $sp = new SemestreParcours($semestre, $parcours);
            // par défaut, isOuvert = true (constructeur le gère)
            $this->em->persist($sp);
            $this->em->flush();
            $this->addFlash('success', 'Semestre inclus');
        } else {
            $this->addFlash('info', 'Semestre déjà inclus');
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/parcours/v2/{parcours}/semestre/{ordre}/toggle-open', name: 'parcours_v2_semestre_toggle_open', methods: ['POST'])]
    public function toggleOpen(Request $request, Parcours $parcours, int $ordre): Response
    {
        $token = $request->request->get('_token');
        if (!$this->csrf->isTokenValid(new CsrfToken('toggle-semestre' . $parcours->getId() . '-' . $ordre, $token))) {
            $this->addFlash('error', 'Token invalide');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
        }

        $sp = $this->em->getRepository(SemestreParcours::class)->findOneBy(['parcours' => $parcours, 'ordre' => $ordre]);
        if (!$sp) {
            $this->addFlash('error', 'Semestre non inclus');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
        }

        $sp->setOuvert(!$sp->isOuvert());
        $this->em->flush();

        $this->addFlash('success', 'État du semestre mis à jour');

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }
}

