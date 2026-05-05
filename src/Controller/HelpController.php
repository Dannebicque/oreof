<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file //wsl.localhost/Ubuntu/home/louca/oreof-stack/oreofv2/src/Controller/HelpController.php
 * @author louca
 * @project oreofv2
 * @lastUpdate 04/05/2026 16:27
 */

namespace App\Controller;

use App\Entity\Help;
use App\Entity\User;
use App\Repository\HelpRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HelpController extends AbstractController
{
    #[Route('/aide', name: 'app_help')]
    public function index(HelpRepository $helpRepository): Response
    {
        $helps = $helpRepository->findBy(['isActive' => true], ['title' => 'ASC']);
        $helps = array_values(array_filter($helps, fn (Help $help) => $this->canAccessHelp($help)));

        return $this->render('help/index.html.twig', [
            'helps' => $helps,
        ]);
    }

    private function canAccessHelp(Help $help): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($help->getProfilsAutorises()->isEmpty()) {
            return true;
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $userProfilIds = [];
        foreach ($user->getUserProfils() as $userProfil) {
            $profil = $userProfil->getProfil();
            if ($profil !== null && $profil->getId() !== null) {
                $userProfilIds[$profil->getId()] = true;
            }
        }

        foreach ($help->getProfilsAutorises() as $profilAutorise) {
            if ($profilAutorise->getId() !== null && isset($userProfilIds[$profilAutorise->getId()])) {
                return true;
            }
        }

        return false;
    }
}

