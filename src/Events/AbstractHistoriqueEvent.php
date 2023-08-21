<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/AbstractHistoriqueEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/08/2023 08:41
 */

namespace App\Events;

use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractHistoriqueEvent
{
    protected UserInterface $user;
    protected ?string $etat = '';
    protected ?string $commentaire = '';
    protected ?string $etape = '';

    /**
     * @param UserInterface $user
     * @param string|null $etat
     * @param string|null $commentaire
     * @param string|null $etape
     */
    public function __construct(UserInterface $user, ?string $etape, ?string $etat, ?string $commentaire = '')
    {
        $this->user = $user;
        $this->etat = $etat;
        $this->commentaire = $commentaire;
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

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function getEtape(): ?string
    {
        return $this->etape;
    }


}
