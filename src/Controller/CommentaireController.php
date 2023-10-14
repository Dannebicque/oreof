<?php

namespace App\Controller;

use App\Classes\GetCommentaires;
use App\Classes\JsonReponse;
use App\Entity\Commentaire;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentaireController extends AbstractController
{
    #[Route('/commentaire', name: 'app_commentaire')]
    public function index(): Response
    {
        return $this->render('commentaire/index.html.twig', [
        ]);
    }

    #[Route('/commentaire/ajout', name: 'app_commentaire_ajout')]
    public function ajout(
        GetCommentaires $getCommentaires,
        Request         $request
    ): Response
    {
        $id = $request->query->get('id');
        $type = $request->query->get('type');
        $zone = $request->query->get('zone');

        if ($request->isMethod('POST')) {
            $getCommentaires->ajoutCommentaire($id, $type, $zone, $request->request->get('message'), $this->getUser());

            return JsonReponse::success('Commentaire ajouté');
        }


        return $this->render('commentaire/_ajout.html.twig', [
            'id' => $id,
            'type' => $type,
            'zone' => $zone,
        ]);
    }

    #[Route('/commentaire/liste', name: 'app_commentaire_liste')]
    public function liste(
        GetCommentaires $getCommentaires,
        Request         $request
    ): Response
    {
        $id = $request->query->get('id');
        $type = $request->query->get('type');
        $zone = $request->query->get('zone');

        $commentaires = $getCommentaires->getCommentairesByUser($id, $type, $zone, $this->getUser());

        return $this->render('commentaire/_liste.html.twig', [
            'commentaires' => $commentaires
        ]);
    }

    #[Route('/commentaire/delete/{id}', name: 'app_commentaire_delete', methods: ['DELETE'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Commentaire            $commentaire
    ): Response
    {
        $entityManager->remove($commentaire);
        $entityManager->flush();

        return JsonReponse::success('Commentaire supprimé');
    }
}
