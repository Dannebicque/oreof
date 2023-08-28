<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/AbstractHistoriqueEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/08/2023 08:41
 */

namespace App\Events;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractHistoriqueEvent
{
    protected UserInterface $user;
    protected string $etat = '';
    protected Request $request;
    protected string $etape = '';


    public function __construct(UserInterface $user, string $etape, string $etat, Request $request = null)
    {
        $this->user = $user;
        $this->etat = $etat;
        $this->request = $request;
        $this->etape = $etape;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function getEtape(): ?string
    {
        return $this->etape;
    }


}
