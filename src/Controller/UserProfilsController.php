<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/UserProfilsController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/05/2025 16:32
 */

namespace App\Controller;

use App\Entity\Profil;
use App\Entity\User;
use App\Enums\CentreGestionEnum;
use App\Repository\ComposanteRepository;
use App\Repository\EtablissementRepository;
use App\Repository\FormationRepository;
use App\Repository\ProfilRepository;
use App\Utils\JsonRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/gestion/user/profils', name: 'app_user_profils_')]
final class UserProfilsController extends BaseController
{
    #[Route('/{user}', name: 'gestion')]
    public function gestionUserProfils(
        ProfilRepository $profilRepository,
        User             $user
    ): Response
    {
        return $this->render('user_profils/_gestion_profils.html.twig', [
            'user' => $user,
            'profils' => $profilRepository->findAll()
        ]);
    }

    #[Route('/{user}/liste', name: 'liste')]
    public function listeUserProfils(
        ProfilRepository $profilRepository,
        User             $user
    ): Response
    {
        return $this->render('user_profils/_liste_profils.html.twig', [
            'user' => $user,
            'userProfils' => $user->getUserProfils()
        ]);
    }

    #[Route('/{user}/add', name: 'add')]
    public function addUserProfils(
        ProfilRepository $profilRepository,
        User             $user
    ): Response
    {
        return $this->render('user_profils/_gestion_profils.html.twig', [
            'user' => $user,
            'userProfils' => $user->getUserProfils(),
            'profils' => $profilRepository->findAll()
        ]);
    }

    #[Route('/config-profil/{user}', name: 'config')]
    public function configProfil(
        FormationRepository     $formationRepository,
        ComposanteRepository    $composanteRepository,
        EtablissementRepository $etablissementRepository,
        User                    $user,
        Request                 $request,
        ProfilRepository        $profilRepository,
    ): Response
    {
        $profilId = JsonRequest::getValueFromRequest($request, 'profilId');
        $profil = $profilRepository->find($profilId);

        if ($profil !== null) {
            return match ($profil->getCentre()) {
                CentreGestionEnum::CENTRE_GESTION_ETABLISSEMENT => $this->render('user_profils/_config_profil_etablissement.html.twig', [
                    'user' => $user,
                    'profil' => $profil,
                    'etablissements' => $etablissementRepository->findAll()
                ]),
                CentreGestionEnum::CENTRE_GESTION_COMPOSANTE => $this->render('user_profils/_config_profil_composante.html.twig', [
                    'user' => $user,
                    'profil' => $profil,
                    'composantes' => $composanteRepository->findAll()
                ]),
                CentreGestionEnum::CENTRE_GESTION_FORMATION => $this->render('user_profils/_config_profil_formation.html.twig', [
                    'user' => $user,
                    'profil' => $profil,
                    'formations' => $formationRepository->findByCampagneCollecte($this->getCampagneCollecte())
                ]),
                CentreGestionEnum::CENTRE_GESTION_PARCOURS => $this->render('user_profils/_config_profil_parcours.html.twig', []),
                default => $this->render('communs/_erreur.html.twig', [
                    'message' => 'Le centre de gestion n\'est pas reconnu'
                ]),
            };


        }

        return $this->render('communs/_erreur.html.twig', [
            'message' => 'Le profil n\'existe pas'
        ])->setStatusCode(Response::HTTP_NOT_FOUND);
    }

    //app_user_profils_delete
    #[Route('/{profil}/delete', name: 'delete', methods: ['POST', 'DELETE'])]
    public function deleteUserProfils(
        Request $request,
        Profil  $profil
    ): Response
    {

    }

}
