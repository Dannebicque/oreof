<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Config/NoticationListeController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/03/2023 12:18
 */

namespace App\Controller\Config;

use App\Repository\NotificationListeRepository;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NoticationListeController extends AbstractController
{
    #[Route('/notication/liste', name: 'app_notication_liste')]
    public function index(
        NotificationListeRepository $notificationListeRepository
    ): Response {
        return $this->render('notication_liste/index.html.twig', [
            'notifications' => $notificationListeRepository->findAll(),
        ]);
    }

    #[Route('/notication/liste/mise-a-jour', name: 'app_notification_liste_mise_a_jour', methods: ['POST'])]
    public function miseAJour(
        Request $request,
        NotificationListeRepository $notificationListeRepository
    ): Response {
        $data = JsonRequest::getFromRequest($request);
        $idNotif = $data['idNotif'];
        $toNotif = $data['toNotif'];
        $value = (bool)$data['value'];

        $notif = $notificationListeRepository->find($idNotif);
        if ($notif === null) {
            return $this->json(false);
        }
        $set = 'set' . ucfirst($toNotif);
        $notif->$set($value);

        $notificationListeRepository->save($notif, true);

        return $this->json(true);
    }
}
