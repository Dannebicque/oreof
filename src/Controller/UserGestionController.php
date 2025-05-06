<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/UserGestionController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\Ldap;
use App\Entity\User;
use App\Entity\UserCentre;
use App\Enums\CentreGestionEnum;
use App\Events\AddCentreFormationEvent;
use App\Events\AddCentreParcoursEvent;
use App\Events\NotifCentreComposanteEvent;
use App\Events\NotifCentreEtablissementEvent;
use App\Events\NotifCentreFormationEvent;
use App\Events\UserEvent;
use App\Form\UserAddType;
use App\Repository\ComposanteRepository;
use App\Repository\EtablissementRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use App\Repository\RoleRepository;
use App\Repository\UserCentreRepository;
use App\Repository\UserRepository;
use App\Utils\JsonRequest;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Route('/administration/user/gestion')]
class UserGestionController extends BaseController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly UserRepository           $userRepository,
    ) {
    }

    #[Route('/ajout/utilisateur', name: 'app_user_missing')]
    public function ajoutUtilisateur(
        Ldap                   $ldap,
        EntityManagerInterface $entityManager,
        ParcoursRepository     $parcoursRepository,
        FicheMatiereRepository $ficheMatiereRepository,
        FormationRepository    $formationRepository,
        UserRepository         $userRepository,
        Request                $request
    ): Response {
        $user = new  User();

        $form = $this->createForm(UserAddType::class, $user, [
            'action' => $this->generateUrl(
                'app_user_missing',
                ['action' => $request->query->get('action'), 'id' => $request->query->get('id')]
            ),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            //Vérifier qu'il n'existe pas
            $exist = $userRepository->findOneBy(['email' => $email]);
            if ($exist !== null) {
                return $this->json('Cet utilisateur existe déjà !', 500);
            }

            //récupération depuis le LDAP
            $ldapUser = $ldap->getDatas($email);
            if ($ldapUser === null) {
                return $this->json('Cet utilisateur n\'existe pas dans le LDAP !', 500);
            }

//            $ldapUser = [
//                'nom' => 'testaaa',
//                'prenom' => 'test',
//                'username' => 'test' . md5(random_bytes(10)),
//            ];

            //ajout des données
            $user->setNom($ldapUser['nom']);
            $user->setPrenom($ldapUser['prenom']);
            $user->setUsername($ldapUser['username']);
            $user->setIsEnable(true);
            $user->setIsValideAdministration(true);
            $entityManager->persist($user);
            //ajout des droits et du centre

            switch ($request->query->get('action')) {
                case 'responsableFicheMatiere':
                    $fiche = $ficheMatiereRepository->find($request->query->get('id'));
                    //pas besoin d'envoyer un mail dans ce cas
                    if ($fiche !== null) {
                        $fiche->setResponsableFicheMatiere($user);
                        $entityManager->flush();

                        return $this->json(true);
                    }

                    return $this->json('Une erreur est survenue !', 500);

                case 'responsableParcours':
                    $parcours = $parcoursRepository->find($request->query->get('id'));
                    if ($parcours !== null) {
                        // retirer l'ancien resp des centres et droits et envoyer mail
                        $event = new AddCentreParcoursEvent($parcours, ['ROLE_RESP_PARCOURS'], $parcours->getRespParcours(), $this->getCampagneCollecte());
                        $this->eventDispatcher->dispatch($event, AddCentreParcoursEvent::REMOVE_CENTRE_PARCOURS);
                        // ajouter le nouveau resp, ajouter centre et droits et envoyer mail
                        $event = new AddCentreParcoursEvent(
                            $parcours,
                            ['ROLE_RESP_PARCOURS'],
                            $user, $this->getCampagneCollecte()
                        );
                        $this->eventDispatcher->dispatch($event, AddCentreParcoursEvent::ADD_CENTRE_PARCOURS);

                        $parcours->setCoResponsable($user);
                        $entityManager->flush();

                        return $this->json(true);
                    }

                    return $this->json('Une erreur est survenue !', 500);
                case 'responsableFormation':
                    $formation = $formationRepository->find($request->query->get('id'));
                    if ($formation !== null) {
                        // retirer l'ancien resp des centres et droits et envoyer mail
                        $event = new AddCentreFormationEvent($formation, $formation->getResponsableMention(), ['ROLE_RESP_FORMATION'], $this->getCampagneCollecte());
                        $this->eventDispatcher->dispatch($event, AddCentreFormationEvent::REMOVE_CENTRE_FORMATION);
                        // ajouter le nouveau resp, ajouter centre et droits et envoyer mail
                        $event = new AddCentreFormationEvent(
                            $formation,
                            $user,
                            ['ROLE_RESP_FORMATION'], $this->getCampagneCollecte()
                        );
                        $this->eventDispatcher->dispatch($event, AddCentreFormationEvent::ADD_CENTRE_FORMATION);

                        $formation->setResponsableMention($user);
                        $entityManager->flush();

                        return $this->json(true);
                    }

                    return $this->json('Une erreur est survenue !', 500);
                case 'coResponsableParcours':
                    $parcours = $parcoursRepository->find($request->query->get('id'));
                    if ($parcours !== null) {
                        // retirer l'ancien resp des centres et droits et envoyer mail
                        $event = new AddCentreParcoursEvent($parcours, ['ROLE_CO_RESP_PARCOURS'], $parcours->getCoResponsable(), $this->getCampagneCollecte());
                        $this->eventDispatcher->dispatch($event, AddCentreParcoursEvent::REMOVE_CENTRE_PARCOURS);
                        // ajouter le nouveau resp, ajouter centre et droits et envoyer mail
                        $event = new AddCentreParcoursEvent(
                            $parcours,
                            ['ROLE_CO_RESP_PARCOURS'],
                            $user, $this->getCampagneCollecte()
                        );
                        $this->eventDispatcher->dispatch($event, AddCentreParcoursEvent::ADD_CENTRE_PARCOURS);

                        $parcours->setCoResponsable($user);
                        $entityManager->flush();

                        return $this->json(true);
                    }

                    return $this->json('Une erreur est survenue !', 500);
                case 'coResponsableMention':
                    $formation = $formationRepository->find($request->query->get('id'));
                    if ($formation !== null) {
                        // retirer l'ancien resp des centres et droits et envoyer mail
                        $event = new AddCentreFormationEvent($formation, $formation->getCoResponsable(), ['ROLE_CO_RESP_FORMATION'], $this->getCampagneCollecte());
                        $this->eventDispatcher->dispatch($event, AddCentreFormationEvent::REMOVE_CENTRE_FORMATION);
                        // ajouter le nouveau resp, ajouter centre et droits et envoyer mail
                        $event = new AddCentreFormationEvent(
                            $formation,
                            $user,
                            ['ROLE_CO_RESP_FORMATION'], $this->getCampagneCollecte()
                        );
                        $this->eventDispatcher->dispatch($event, AddCentreFormationEvent::ADD_CENTRE_FORMATION);
                    }
                    $formation->setCoResponsable($user);
                    $entityManager->flush();

                    return $this->json(true);
            }

            return $this->json('Une erreur est survenue !', 500);
        }

        return $this->render('user/add.html.twig', ['form' => $form->createView(),]);
    }

    #[
        Route('/ajout/utilisateur/verification', name: 'app_user_missing_ldap')]
    public function ajoutLdap(): Response
    {
        return $this->render('user/add.html.twig', [
        ]);
    }

    #[Route('/valid/admin/{user}', name: 'app_user_gestion_valid_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function validAdmin(User $user): Response
    {
        if ($user->isIsValidDpe() === false) {
            $user->setDateValideDpe(new DateTime());
            $user->setIsValidDpe(true);
        }

        $user->setIsEnable(true);
        $user->setComposanteDemande(null);
        $user->setEtablissementDemande(null);
        $user->setIsValideAdministration(true);
        $user->setDateValideAdministration(new DateTime());

        $this->userRepository->save($user, true);

        $this->eventDispatcher->dispatch(new UserEvent($user), UserEvent::USER_VALIDE_ADMIN);

        return $this->redirectToRoute('app_user_attente');
    }

    #[Route('/refuser/admin/{user}', name: 'app_user_gestion_refuser_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function refuserAdmin(
        Request $request,
        User $user): Response
    {
        $motif = $request->request->get('motif');
        $this->userRepository->remove($user, true);

        $userEvent = new UserEvent($user);
        $userEvent->setMotif($motif);
        $this->eventDispatcher->dispatch($userEvent, UserEvent::USER_REFUSER_ADMIN);

        return $this->json(true);
    }


    #[Route('/valid/dpe/{user}', name: 'app_user_gestion_valid_dpe')]
    public function validDpe(User $user): Response
    {
        // $this->denyAccessUnlessGranted('CAN_EDIT_CENTRE');//todo: faire sur manage composante
        if (count($user->getUserCentres()) > 0) {
            $user->setDateValideDpe(new DateTime());
            $user->setIsValidDpe(true);
            $user->setDateValideAdministration(new DateTime());

            $this->userRepository->save($user, true);
            $this->eventDispatcher->dispatch(new UserEvent($user), UserEvent::USER_VALIDE_DPE);
            $this->addFlash('success', 'L\'utilisateur a bien été validé !');
            return $this->redirectToRoute('app_user_attente');
        }

        $this->addFlash('error', 'L\'utilisateur doit avoir au moins un centre !');

        return $this->json(false, 500);
    }

    #[Route('/revoque/admin/{user}', name: 'app_user_gestion_revoque_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function revoqueAdmin(User $user): Response
    {
        $user->setIsEnable(false);
        $user->setIsValideAdministration(false);
        $user->setDateValideAdministration(new DateTime());

        $this->userRepository->save($user, true);
        $this->eventDispatcher->dispatch(new UserEvent($user), UserEvent::USER_REVOQUE_ADMIN);

        return $this->redirectToRoute('app_user_attente');
    }

    #[Route('/gestion/centre/{user}', name: 'app_user_gestion_centre')]
    public function gestionCentre(
        RoleRepository $roleRepository,
        User           $user
    ): Response {
//        $this->denyAccessUnlessGranted('CAN_EDIT_CENTRE');

        return $this->render('user/_gestion_centre.html.twig', [
            'user' => $user,
            'centres' => CentreGestionEnum::cases(),
            'centresUser' => $user->getUserCentres(),
            'roles' => $roleRepository->findAll()
        ]);
    }

    #[Route('/liste/centre/{user}', name: 'app_user_gestion_liste')]
    public function listeCentre(User $user): Response
    {
        return $this->render('user/_liste_centre.html.twig', [
            'user' => $user,
            'centres' => CentreGestionEnum::cases(),
            'centresUser' => $user->getUserCentres()
        ]);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/add/centre/{user}', name: 'app_user_gestion_add_centre')]
    public function addCentre(
        EventDispatcherInterface $eventDispatcher,
        RoleRepository          $roleRepository,
        UserCentreRepository    $userCentreRepository,
        ComposanteRepository    $composanteRepository,
        EtablissementRepository $etablissementRepository,
        FormationRepository     $formationRepository,
        Request                 $request,
        User                    $user
    ): Response {
        $data = JsonRequest::getFromRequest($request);
        //todo: ne devrait pas add si un centre existe déjà sur le même type
        $nCentre = new UserCentre();
        $nCentre->setUser($user);

        $role = $roleRepository->find($data['role']);
        if ($role === null) {
            return $this->json(['error' => 'Ce rôle n\'existe pas'], 400);
        }

        $nCentre->addRole($role);


        switch (CentreGestionEnum::from($data['centreType'])) {
            case CentreGestionEnum::CENTRE_GESTION_COMPOSANTE:
                $centre = $composanteRepository->find($data['centreId']);
                if ($centre === null) {
                    return $this->json(['error' => 'Cette composante n\'existe pas'], 400);
                }
                $uc = $userCentreRepository->findOneBy(['user' => $user, 'composante' => $centre]);
                if ($uc !== null) {
                    return $this->json(['error' => 'Ce centre est déjà associé à cet utilisateur'], 400);
                }

                if ($role->getCodeRole() === 'ROLE_DPE' || $role->getCodeRole() === 'ROLE_DIRECTEUR') {
                    $uc = $userCentreRepository->findComposanteWithSameRole($centre, $role);

                    if ($uc !== null && count($uc) > 0) {
                        if ((bool)$data['force'] === false) {
                            return $this->json(['error' => 'already_exist'], 400);
                        }

                        $userCentreRepository->remove($uc[0]); // on supprime l'ancien centre
                        $event = new NotifCentreComposanteEvent($centre, $uc[0]->getUser());
                        $eventDispatcher->dispatch($event, NotifCentreComposanteEvent::NOTIF_REMOVE_CENTRE_COMPOSANTE);
                    }
                }

                $nCentre->setComposante($centre);
                $event = new NotifCentreComposanteEvent($centre, $user, [$role->getCodeRole()]);
                $eventDispatcher->dispatch($event, NotifCentreComposanteEvent::NOTIF_ADD_CENTRE_COMPOSANTE);

                //si centre DPE ou DIRECTEUR... Mettre à jour la composante
                if ($role->getCodeRole() === 'ROLE_DPE') {
                    $centre->setResponsableDpe($user);
                    $composanteRepository->save($centre, true);
                }

                if ($role->getCodeRole() === 'ROLE_DIRECTEUR') {
                    $centre->setDirecteur($user);
                    $composanteRepository->save($centre, true);
                }
                break;
            case CentreGestionEnum::CENTRE_GESTION_ETABLISSEMENT:
                $centre = $etablissementRepository->find(1);
                if ($centre === null) {
                    return $this->json(['error' => 'Cet établissement n\'existe pas'], 400);
                }

                $uc = $userCentreRepository->findOneBy(['user' => $user, 'etablissement' => $centre]);
                if ($uc !== null) {
                    return $this->json(['error' => 'Ce centre est déjà associé à cet utilisateur'], 400);
                }
                $nCentre->setEtablissement($centre);

                $event = new NotifCentreEtablissementEvent($centre, $user, [$role->getCodeRole()]);
                $eventDispatcher->dispatch($event, NotifCentreEtablissementEvent::NOTIF_ADD_CENTRE_ETABLISSEMENT);

                break;
            case CentreGestionEnum::CENTRE_GESTION_FORMATION:
                $centre = $formationRepository->find($data['centreId']);
                if ($centre === null) {
                    return $this->json(['error' => 'Cette formation n\'existe pas'], 400);
                }

//                $uc = $userCentreRepository->findOneBy(['user' => $user, 'formation' => $centre]);
//                if ($uc !== null) {
//                    return $this->json(['error' => 'Ce centre est déjà associé à cet utilisateur'], 400);
//                }

                if ($role->getCodeRole() === 'ROLE_RESP_FORMATION' || $role->getCodeRole() === 'ROLE_CO_RESP_FORMATION') {
                    $uc = $userCentreRepository->findFormationWithSameRole($centre, $role);
                    if ($uc !== null && count($uc) > 0) {
                        if ((bool)$data['force'] === false) {
                            return $this->json(['error' => 'already_exist'], 400);
                        }

                        $userCentreRepository->remove($uc[0]); // on supprime l'ancien centre
                        $event = new NotifCentreFormationEvent($centre, $uc[0]->getUser());
                        $eventDispatcher->dispatch($event, NotifCentreFormationEvent::NOTIF_REMOVE_CENTRE_FORMATION);
                    }
                }

                $nCentre->setFormation($centre);
                $nCentre->setCampagneCollecte($this->getCampagneCollecte());
                $event = new NotifCentreFormationEvent($centre, $user, [$role->getCodeRole()]);
                $eventDispatcher->dispatch($event, NotifCentreFormationEvent::NOTIF_ADD_CENTRE_FORMATION);

                //si centre ROLE_RESP_FORMATION ou ROLE_CO_RESP_FORMATION... Mettre à jour la formation
                if ($role->getCodeRole() === 'ROLE_RESP_FORMATION') {
                    $centre->setResponsableMention($user);
                    $formationRepository->save($centre, true);
                }

                if ($role->getCodeRole() === 'ROLE_CO_RESP_FORMATION') {
                    $centre->setCoResponsable($user);
                    $formationRepository->save($centre, true);
                }

                break;
        }

        $userCentreRepository->save($nCentre, true);

        return $this->json(['success' => 'Centre ajouté avec succès']);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'app_user_gestion_delete_centre', methods: ['DELETE'])]
    public function delete(
        Request              $request,
        UserCentre           $userCentre,
        UserCentreRepository $userCentreRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $userCentre->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            $userCentreRepository->remove($userCentre, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
