<?php

namespace App\Controller\API;

use App\Repository\UserCentreRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/user')]
class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    #[Route('/api/user', name: 'api_user_get_user', methods: ['GET'])]
    public function user(Request $request): Response
    {
        $user = $this->userRepository->find($request->query->get('id'));

        return $this->render('api/user/_user.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/api/users/composante', name: 'api_user_get_all_user_composante', methods: ['GET'])]
    public function usersComposante(
        UserCentreRepository $userCenterRepository,
        Request $request
    ): Response {
        $users = $userCenterRepository->findByComposante((int)$request->query->get('composante'));

        $tab = [];

        foreach ($users as $user) {
            if ($user->getuser() !== null) {
                $tab[] = [
                    'id' => $user->getUser()->getId(),
                    'libelle' => $user->getUser()->getDisplay(),
                ];
            }
        }


        return $this->json($tab);

    }
}
