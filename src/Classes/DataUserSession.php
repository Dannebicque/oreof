<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/DataUserSession.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Classes;

use App\Entity\CampagneCollecte;
use App\Entity\Etablissement;
use App\Repository\CampagneCollecteRepository;
use Stringable;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DataUserSession
{
    private UserInterface $user;

    private string $dir;
    private ?CampagneCollecte $campagneCollecte = null;
    private ?Etablissement $etablissement = null;


    public function __construct(
        private RequestStack               $requestStack,
        private CampagneCollecteRepository $campagneCollecteRepository,
        TokenStorageInterface              $tokenStorage,
        KernelInterface                    $kernel,
    ) {
        $this->dir = $kernel->getProjectDir();
        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    public function getCampagneCollecte(): ?CampagneCollecte
    {
        $session = $this->requestStack->getSession();
        if ($this->campagneCollecte === null) {
            if ($session !== null && $session->get('campagneCollecte') !== null) {
                $this->campagneCollecte = $this->campagneCollecteRepository->find($session->get('campagneCollecte'));
            } else {
                $this->campagneCollecte = $this->campagneCollecteRepository->findOneBy(['defaut' => true]);
            }
        }

        return $this->campagneCollecte;
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

    public function getEtablissement(): ?Etablissement
    {
        if ($this->etablissement === null) {
            $this->etablissement = $this->getUser()?->getEtablissement();
        }

        return $this->etablissement;
    }
}
