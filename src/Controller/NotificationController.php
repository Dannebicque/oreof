<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

    #[Route('/notification/tout-lu', name: 'app_notification_tout_lu')]
    public function toutLu(
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository,
    ): Response {
        $notifications = $notificationRepository->findBy(['destinataire' => $this->getUser()], ['created' => 'DESC']);

        foreach ($notifications as $notification) {
            $notification->setLu(true);
        }
        $entityManager->flush();

        return JsonReponse::success('Notifications marquées comme lues.');
    }

    #[Route('/notification/tout-supprimer', name: 'app_notification_tout_suppr')]
    public function toutSuppr(
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository,
    ): Response {
        $notifications = $notificationRepository->findBy(['destinataire' => $this->getUser()], ['created' => 'DESC']);
        foreach ($notifications as $notification) {
            $notificationRepository->remove($notification);
        }
        $entityManager->flush();

        return JsonReponse::success('Notifications supprimées.');
    }

    #[Route('/notification/liste', name: 'app_notification_liste_user')]
    public function listeUser(
        NotificationRepository $notificationRepository,
        Request                $request
    ): Response {
        $notifications = $notificationRepository->findBy(['destinataire' => $this->getUser()], ['created' => 'DESC']);

        return $this->render('notification/_liste.html.twig', [
            'notifications' => $notifications,
        ]);
    }
}
