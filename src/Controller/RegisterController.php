<?php

namespace App\Controller;

use App\Classes\Ldap;
use App\Entity\User;
use App\Events\UserEvent;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/demande-acces', name: 'app_register')]
    public function index(
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
                $username = $ldap->getUsername($user->getEmail());
                $user->setUsername($username ?? $user->getEmail());
                $user->setDateDemande(new \DateTime());
                $user->setCentreId((int)$request->request->get('selectListe'));

                $userRepository->save($user, true);
                $this->addFlash('success', 'Votre demande a bien été prise en compte');

                $userEvent = new UserEvent($user);
                $eventDispatcher->dispatch($userEvent, UserEvent::USER_DEMANDE_ACCES);

                return $this->render('register/confirm.html.twig', [
                    'user' => $user,
                ]);
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
