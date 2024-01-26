<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/DataUserSession.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Classes;

use App\Entity\Dpe;
use App\Repository\DpeRepository;
use Stringable;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DataUserSession
{
    private UserInterface $user;

    private string $dir;
    private ?Dpe $dpe;


    public function __construct(
        RequestStack      $requestStack,
        DpeRepository         $dpeRepository,
        TokenStorageInterface $tokenStorage,
        KernelInterface       $kernel,
    ) {
        if ($requestStack->getSession()->get('dpe') !== null) {
            $this->dpe = $dpeRepository->find($requestStack->getSession()->get('dpe'));
        } else {
            $this->dpe = $dpeRepository->findOneBy(['defaut' => true]);
        }


        $this->dir = $kernel->getProjectDir();
        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    public function getDpe(): ?Dpe
    {
        return $this->dpe;
    }

    public function version(): ?string
    {
        $filename = $this->dir . '/package.json';
        $composerData = json_decode(file_get_contents($filename), true);

        return $composerData['version'];
    }

    public function getUser(): UserInterface|Stringable|string
    {
        return $this->user;
    }
}
