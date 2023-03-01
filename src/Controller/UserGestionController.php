<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserCentre;
use App\Enums\CentreGestionEnum;
use App\Events\UserEvent;
use App\Repository\ComposanteRepository;
use App\Repository\EtablissementRepository;
use App\Repository\FormationRepository;
use App\Repository\RoleRepository;
use App\Repository\UserCentreRepository;
use App\Repository\UserRepository;
use App\Utils\JsonRequest;
use DateTime;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration/user/gestion')]
class UserGestionController extends BaseController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/valid/admin/{user}', name: 'app_user_gestion_valid_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function validAdmin(User $user): Response
    {
        if ($user->isIsValidDpe() === false) {
            $user->setDateValideDpe(new DateTime());
            $user->setIsValidDpe(true);
        }

        $user->setIsEnable(true);
        $user->setIsValideAdministration(true);
        $user->setDateValideAdministration(new DateTime());

        $this->userRepository->save($user, true);

        $this->eventDispatcher->dispatch(new UserEvent($user), UserEvent::USER_VALIDE_ADMIN);

        return $this->redirectToRoute('app_user_attente');
    }

    #[Route('/valid/dpe/{user}', name: 'app_user_gestion_valid_dpe')]
    #[IsGranted('ROLE_RESP_DPE')]
    public function validDpe(User $user): Response
    {
        $user->setDateValideDpe(new DateTime());
        $user->setIsValidDpe(true);
        $user->setDateValideAdministration(new DateTime());

        $this->userRepository->save($user, true);
        $this->eventDispatcher->dispatch(new UserEvent($user), UserEvent::USER_VALIDE_DPE);

        return $this->redirectToRoute('app_user_attente');
    }

    #[Route('/revoque/admin/{user}', name: 'app_user_gestion_revoque_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function revoqueAdmin(User $user): Response
    {
        $user->setIsEnable(false);
        $user->setIsValideAdministration(false);
        $user->setDateValideAdministration(new DateTime());

        $this->userRepository->save($user, true);
        $this->eventDispatcher->dispatch(new UserEvent($user), UserEvent::USER_REVOQUE_ADMIN);

        return $this->redirectToRoute('app_user_attente');
    }

    #[Route('/droits/{user}', name: 'app_user_gestion_droits')]
    #[IsGranted('ROLE_ADMIN')]
    public function gestionDroits(
        RoleRepository $roleRepository,
        User $user): Response
    {
        return $this->render('user/_gestion_droits.html.twig', [
            'user' => $user,
            'roles' => $roleRepository->findByAll()
        ]);
    }

    #[Route('/gestion/centre/{user}', name: 'app_user_gestion_centre')]
    #[IsGranted('ROLE_ADMIN')]
    public function gestionCentre(
        RoleRepository $roleRepository,
        User $user): Response
    {
        return $this->render('user/_gestion_centre.html.twig', [
            'user' => $user,
            'centres' => CentreGestionEnum::cases(),
            'centresUser' => $user->getUserCentres(),
            'roles' => $roleRepository->findAll()
        ]);
    }

    #[Route('/liste/centre/{user}', name: 'app_user_gestion_liste')]
    #[IsGranted('ROLE_ADMIN')]
    public function listeCentre(User $user): Response
    {
        return $this->render('user/_liste_centre.html.twig', [
            'user' => $user,
            'centres' => CentreGestionEnum::cases(),
            'centresUser' => $user->getUserCentres()
        ]);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/add/centre/{user}', name: 'app_user_gestion_add_centre')]
    #[IsGranted('ROLE_ADMIN')]
    public function addCentre(
        RoleRepository $roleRepository,
        UserCentreRepository $userCentreRepository,
        ComposanteRepository $composanteRepository,
        EtablissementRepository $etablissementRepository,
        FormationRepository $formationRepository,
        Request $request, User $user): Response
    {
        $data = JsonRequest::getFromRequest($request);
        $nCentre = new UserCentre();
        $nCentre->setUser($user);

        $role = $roleRepository->find($data['role']);
        $nCentre->addRole($role);

        switch (CentreGestionEnum::from($data['centreType'])) {
            case CentreGestionEnum::CENTRE_GESTION_COMPOSANTE:
                $centre = $composanteRepository->find($data['centreId']);
                $uc = $userCentreRepository->findOneBy(['user' => $user, 'composante' => $centre]);
                if ($uc !== null) {
                    return $this->json(['error' => 'Ce centre est déjà associé à cet utilisateur'], 400);
                }
                $nCentre->setComposante($centre);
                break;
            case CentreGestionEnum::CENTRE_GESTION_ETABLISSEMENT:
                $centre = $etablissementRepository->find(1);
                $uc = $userCentreRepository->findOneBy(['user' => $user, 'etablissement' => $centre]);
                if ($uc !== null) {
                    return $this->json(['error' => 'Ce centre est déjà associé à cet utilisateur'], 400);
                }
                $nCentre->setEtablissement($centre);
                break;
            case CentreGestionEnum::CENTRE_GESTION_FORMATION:
                $centre = $formationRepository->find($data['centreId']);
                $uc = $userCentreRepository->findOneBy(['user' => $user, 'formation' => $centre]);
                if ($uc !== null) {
                    return $this->json(['error' => 'Ce centre est déjà associé à cet utilisateur'], 400);
                }
                $nCentre->setFormation($centre);
                break;
        }

        $userCentreRepository->save($nCentre, true);
        return $this->json(['success' => 'Centre ajouté avec succès']);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'app_user_gestion_delete_centre', methods: ['DELETE'])]
    public function delete(
        Request $request,
        UserCentre $userCentre,
        UserCentreRepository $userCentreRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $userCentre->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf'))) {
            $userCentreRepository->remove($userCentre, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
