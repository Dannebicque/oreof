<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/DataUserSession.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Classes;

use App\Entity\AnneeUniversitaire;
use App\Repository\AnneeUniversitaireRepository;
use Stringable;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DataUserSession
{
    private UserInterface $user;

    private string $dir;
    private ?AnneeUniversitaire $anneeUniversitaire;


    public function __construct(
        AnneeUniversitaireRepository $anneeUniversitaireRepository,
        TokenStorageInterface $tokenStorage,
        KernelInterface $kernel,
    ) {
        $this->anneeUniversitaire = $anneeUniversitaireRepository->findOneBy(['defaut' => true]);
        $this->dir = $kernel->getProjectDir();
        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    public function getAnneeUniversitaire(): ?AnneeUniversitaire
    {
        return $this->anneeUniversitaire;
    }

    public function version(): string
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
