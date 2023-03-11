<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/utilisateur/mes-informations', name: 'app_user_mes_informations')]
    public function mesInformations(): Response
    {
        return $this->render('user/mes-informations.html.twig', [
            'centres' => $this->getUser()->getUserCentres(),
        ]);
    }

    #[Route('/utilisateur/mes-notifications', name: 'app_user_mes_notifications')]
    public function mesNotifications(): Response
    {
        return $this->render('user/mes-notifications.html.twig', [
        ]);
    }
}
