<?php

namespace App\Controller;

use App\Entity\User;
use App\Events\UserEvent;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration/user/gestion')]
class UserGestionController extends BaseController
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private UserRepository $userRepository,
    ) {
    }

    #[Route('/valid/admin/{user}', name: 'app_user_gestion_valid_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function validAdmin(User $user): Response
    {
        if ($user->isIsValidDpe() === false) {
            $user->setDateValideDpe(new \DateTime());
            $user->setIsValidDpe(true);
        }

        $user->setIsEnable(true);
        $user->setIsValideAdministration(true);
        $user->setDateValideAdministration(new \DateTime());

        $this->userRepository->save($user, true);

        $this->eventDispatcher->dispatch(new UserEvent($user), UserEvent::USER_VALIDE_ADMIN);

        return $this->redirectToRoute('app_user_attente');
    }

    #[Route('/valid/dpe/{user}', name: 'app_user_gestion_valid_dpe')]
    #[IsGranted('ROLE_RESP_DPE')]
    public function validDpe(User $user): Response
    {
        $user->setDateValideDpe(new \DateTime());
        $user->setIsValidDpe(true);
        $user->setDateValideAdministration(new \DateTime());

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
        $user->setDateValideAdministration(new \DateTime());

        $this->userRepository->save($user, true);
        $this->eventDispatcher->dispatch(new UserEvent($user), UserEvent::USER_REVOQUE_ADMIN);

        return $this->redirectToRoute('app_user_attente');
    }
}
