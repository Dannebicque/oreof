<?php

namespace App\Controller\Config;

use App\Classes\Ldap;
use App\Entity\User;
use App\Entity\UserCentre;
use App\Enums\CentreGestionEnum;
use App\Enums\RoleEnum;
use App\Events\UserEvent;
use App\Form\UserLdapType;
use App\Form\UserType;
use App\Repository\ComposanteRepository;
use App\Repository\EtablissementRepository;
use App\Repository\FormationRepository;
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

    #[Route('/ajouter-ldap', name: 'app_user_new_ldap', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function newLdap(
        EtablissementRepository $etablissementRepository,
        UserCentreRepository $userCentreRepository,
        ComposanteRepository $composanteRepository,
        FormationRepository $formationRepository,
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

            //todo: fusionner avec Register
            $centre = $form['centreDemande']->getData();
            switch ($centre) {
                case CentreGestionEnum::CENTRE_GESTION_FORMATION:
                    $formation = $formationRepository->find($request->request->get('selectListe'));
                    $centreUser = new UserCentre();
                    $centreUser->setUser($user);
                    $centreUser->setFormation($formation);
                    break;
                case CentreGestionEnum::CENTRE_GESTION_COMPOSANTE:
                    $composante = $composanteRepository->find($request->request->get('selectListe'));
                    $centreUser = new UserCentre();
                    $centreUser->setUser($user);
                    $centreUser->setComposante($composante);
                    break;
                case CentreGestionEnum::CENTRE_GESTION_ETABLISSEMENT:
                    $etablissement = $etablissementRepository->find(1);//todo: imposé car juste URCA
                    $centreUser = new UserCentre();
                    $centreUser->setUser($user);
                    $centreUser->setEtablissement($etablissement);
                    break;
            }

            $userCentreRepository->save($centreUser, true);

            $this->addFlash('success', 'L\'utilisateur a été ajouté avec succès');

            if ($form['sendMail']->getData()) {
                $userEvent = new UserEvent($user);
                $eventDispatcher->dispatch($userEvent, UserEvent::USER_AJOUTE);
            }

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
            'roles' => RoleEnum::cases()
        ]);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/change-role/{id}', name: 'app_user_roles', methods: ['POST'])]
    public function changeRole(
        Request $request,
        UserRepository $userRepository,
        User $user): Response
    {
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
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user,
            [
                'action' => $this->generateUrl('app_user_edit', ['id' => $user->getId()]),
            ]);
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
