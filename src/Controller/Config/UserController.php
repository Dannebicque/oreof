<?php

namespace App\Controller\Config;

use App\Classes\Ldap;
use App\Entity\User;
use App\Events\UserEvent;
use App\Form\UserLdapType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration/utilisateurs')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('config/user/index.html.twig', [
        ]);
    }

    #[Route('/attente-validation', name: 'app_user_attente', methods: ['GET'])]

    public function attente(UserRepository $repository): Response
    {
        //todo: gérer par le responsable de DPE ?? pour affecter les droits et "pré-valider" les utilisateurs
        $users = $repository->findNotEnableAvecDemande();

        return $this->render('config/user/attente.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/liste', name: 'app_user_liste', methods: ['GET'])]
    public function liste(UserRepository $userRepository): Response
    {
        return $this->render('config/user/_liste.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'action' => $this->generateUrl('app_user_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

//todo: gérer LDAP => champ pour rechercher par email / login
            return $this->json(true);
        }

        return $this->render('config/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ajouter-ldap', name: 'app_user_new_ldap', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function newLdap(
        Ldap $ldap,
        EventDispatcherInterface $eventDispatcher,
        Request $request, UserRepository $userRepository): Response
    {

        $user = new User();
        $form = $this->createForm(UserLdapType::class, $user, [
            'action' => $this->generateUrl('app_user_new_ldap'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dataUsers = $ldap->getDatas($user->getEmail());
            $user->setUsername($dataUsers['username']);
            $user->setNom($dataUsers['nom']);
            $user->setPrenom($dataUsers['prenom']);
            $user->setRoles([strtoupper($request->request->all()['user_ldap']['role'])]);
            $userRepository->save($user, true);

            $this->addFlash('success', 'L\'utilisateur a été ajouté avec succès');

            $userEvent = new UserEvent($user);
            $eventDispatcher->dispatch($userEvent, UserEvent::USER_AJOUTE);
            return $this->json(true);
        }

        return $this->render('config/user/new-ldap.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('config/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user,
            [
                'action' => $this->generateUrl('app_user_edit', ['id' => $user->getId()]),
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->json(true);
        }

        return $this->render('config/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
