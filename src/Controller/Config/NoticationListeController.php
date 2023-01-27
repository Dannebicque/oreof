<?php

namespace App\Controller\Config;

use App\Repository\NotificationListeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NoticationListeController extends AbstractController
{
    #[Route('/notication/liste', name: 'app_notication_liste')]
    public function index(
        NotificationListeRepository $notificationListeRepository
    ): Response
    {
        return $this->render('notication_liste/index.html.twig', [
            'notifications' => $notificationListeRepository->findAll(),
        ]);
    }
}
