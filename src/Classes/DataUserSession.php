<?php

namespace App\Classes;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DataUserSession
{
    private UserInterface $user;

    private string $dir;


    public function __construct(
        TokenStorageInterface $tokenStorage,
        KernelInterface $kernel,
    ) {
        $this->dir = $kernel->getProjectDir();
        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    public function version()
    {
        $filename = $this->dir . '/package.json';
        $composerData = json_decode(file_get_contents($filename), true);

        return $composerData['version'];
    }

    /**
     * @return string|\Stringable|\Symfony\Component\Security\Core\User\UserInterface
     */
    public function getUser(): UserInterface|\Stringable|string
    {
        return $this->user;
    }
}
