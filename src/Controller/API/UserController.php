<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/user')]
class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
    )
    {
    }

    #[Route('/api/user', name: 'api_user_get_user', methods: ['GET'])]
    public function user(Request $request): Response
    {
        $user = $this->userRepository->find($request->query->get('id'));
        return $this->render('api/user/_user.html.twig', [
            'user' => $user,
        ]);
    }
}
