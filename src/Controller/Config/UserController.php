<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Config/UserController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Config;

use App\Classes\Ldap;
use App\Entity\User;
use App\Entity\UserCentre;
use App\Enums\CentreGestionEnum;
use App\Events\UserEvent;
use App\Form\UserLdapType;
use App\Form\UserType;
use App\Repository\ComposanteRepository;
use App\Repository\EtablissementRepository;
use App\Repository\FormationRepository;
use App\Repository\RoleRepository;
use App\Repository\UserCentreRepository;
use App\Repository\UserRepository;
use App\Utils\JsonRequest;
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
        return $this->render('config/user/index.html.twig');
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
    public function liste(
        Request $request,
        UserRepository $userRepository
    ): Response {
        $sort = $request->query->get('sort') ?? 'nom';
        $direction = $request->query->get('direction') ?? 'asc';
        $q = $request->query->get('q') ?? null;

        if ($q) {
            $users = $userRepository->findEnableBySearch($q, $sort, $direction);
        } else {
            $users = $userRepository->findEnable($sort, $direction);
        }

        return $this->render('config/user/_liste.html.twig', [
            'users' => $users,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    #[Route('/ajouter-ldap', name: 'app_user_new_ldap', methods: ['GET'])]
    #[IsGranted('ROLE_SES')]
    public function newLdap(): Response {
        $user = new User();
        $form = $this->createForm(UserLdapType::class, $user, [
            'action' => $this->generateUrl('app_user_new_ldap'),
        ]);

        return $this->render('config/user/new-ldap.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ajouter-ldap', name: 'app_user_new_ldap_valide', methods: ['POST'])]
    #[IsGranted('ROLE_SES')]
    public function saveLdap(
        Ldap $ldap,
        Request $request,
        UserRepository $userRepository
    ): Response {
        $email = $request->request->get('user_ldap_email');
        $user = new User();

        $dataUsers = $ldap->getDatas($email);
        $user->setUsername($dataUsers['username']);
        $user->setEmail($email);
        $user->setIsEnable(true);
        $user->setIsValideAdministration(true);
        $user->setNom($dataUsers['nom']);
        $user->setPrenom($dataUsers['prenom']);
        $userRepository->save($user, true);

        $this->addFlash('success', 'L\'utilisateur a été ajouté avec succès');

        return $this->json([
            'success' => true,
            'url' => $this->generateUrl('app_user_gestion_centre', ['user' => $user->getId()])
        ]);
    }


    #[
        Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(
        RoleRepository $roleRepository,
        User $user
    ): Response {
        return $this->render('config/user/show.html.twig', [
            'user' => $user,
            'roles' => $roleRepository->findByAll()
        ]);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/change-role/{id}', name: 'app_user_roles', methods: ['POST'])]
    public function changeRole(
        Request $request,
        UserRepository $userRepository,
        User $user
    ): Response {
        $data = JsonRequest::getFromRequest($request);
        $roles = $user->getRoles();

        if ($data['checked']) {
            $roles[] = $data['role'];
        } else {
            $roles = array_diff($roles, [$data['role']]);
        }
        $user->setRoles($roles);
        $userRepository->save($user, true);

        return $this->json(true);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_SES')]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(
            UserType::class,
            $user,
            [
                'action' => $this->generateUrl('app_user_edit', ['id' => $user->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles([strtoupper($request->request->all()['user']['role'])]);
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
