<?php

namespace App\Controller;

use App\Classes\Ldap;
use App\Entity\User;
use App\Entity\UserCentre;
use App\Enums\CentreGestionEnum;
use App\Events\UserRegisterEvent;
use App\Form\RegisterType;
use App\Repository\ComposanteRepository;
use App\Repository\EtablissementRepository;
use App\Repository\FormationRepository;
use App\Repository\UserCentreRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/demande-acces', name: 'app_register')]
    public function index(
        EtablissementRepository $etablissementRepository,
        UserCentreRepository $userCentreRepository,
        ComposanteRepository $composanteRepository,
        Ldap $ldap,
        EventDispatcherInterface $eventDispatcher,
        UserRepository $userRepository,
        Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($existUser === null) {
                $username = null; //$ldap->getUsername($user->getEmail());
                $user->setUsername($username ?? $user->getEmail());
                $user->setDateDemande(new DateTime());
                $userRepository->save($user, true);

                $centre = $form['centreDemande']->getData();
                $userEvent = new UserRegisterEvent($user, $centre);
                switch ($centre) {
                    case CentreGestionEnum::CENTRE_GESTION_COMPOSANTE:
                        $composante = $composanteRepository->find($request->request->get('selectListe'));
                        $centreUser = new UserCentre();
                        $centreUser->setUser($user);
                        $centreUser->setComposante($composante);
                        $userEvent->setComposante($composante);
                        break;
                    case CentreGestionEnum::CENTRE_GESTION_ETABLISSEMENT:
                        $etablissement = $etablissementRepository->find(1);//todo: imposé car juste URCA
                        $centreUser = new UserCentre();
                        $centreUser->setUser($user);
                        $centreUser->setEtablissement($etablissement);
                        $userEvent->setEtablissement($etablissement);
                        break;
                }

                if (isset($centreUser)) {
                    $userCentreRepository->save($centreUser, true);
                    $this->addFlash('success', 'Votre demande a bien été prise en compte');

                    $eventDispatcher->dispatch($userEvent, UserRegisterEvent::USER_DEMANDE_ACCES);

                    return $this->render('register/confirm.html.twig', [
                        'user' => $user,
                    ]);
                }

                $this->addFlash('Erreur', 'Une erreur est survenue, le centre n\'existe pas');

                return $this->redirectToRoute('app_register');
            }

            $this->addFlash('danger', 'Cet utilisateur existe déjà');

            return $this->render('register/compte_existe.html.twig', [
                'user' => $existUser,
            ]);
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
