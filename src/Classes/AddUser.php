<?php

namespace App\Classes;

use App\Entity\Composante;
use App\Entity\User;
use App\Entity\UserCentre;
use App\Repository\UserCentreRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AddUser
{
    public function __construct(
        private readonly UserCentreRepository $userCentreRepository,
        private readonly UserRepository $userRepository,
        private readonly Ldap $ldap)
    {
    }

    public function addUser(string $email): ?UserInterface
    {
        $exist = $this->userRepository->findOneBy(['email' => $email]);
        if ($exist) {
            return $exist;
        }

        $user = $this->ldap->getDatas($email);
        if ($user) {
            $usr = new User();
            $usr->setUsername($user['username']);
            $usr->setEmail($email);
            $usr->setNom($user['nom']);
            $usr->setPrenom($user['prenom']);
            $this->userRepository->save($usr, true);

            return $usr;
        }

        return null;
    }

    public function addRole(UserInterface $user, string $role): void
    {
        $user->setRoles([$role]);
    }

    public function setCentreComposante(UserInterface $usr, Composante $composante, ?string $role = null): void
    {
        $centre = new UserCentre();
        $centre->setUser($usr);
        $centre->setComposante($composante);
        if ($role !== null) {
            $centre->addRoleCode($role);
        }
        $this->userCentreRepository->save($centre, true);
    }
}
