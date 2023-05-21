<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/notification/lu', name: 'app_notification_lu')]
    public function lu(
        NotificationRepository $notificationRepository,
        Request                $request
    ): Response {
        $notification = $notificationRepository->find($request->query->get('id'));
        if ($notification !== null) {
            $notification->setLu(true);
            $notificationRepository->save($notification, true);

            return $this->json(true);
        }

        return $this->json(false, 500);
    }
}
