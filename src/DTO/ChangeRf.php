<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/ChangeRf.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/05/2024 17:25
 */

namespace App\DTO;

use App\Entity\User;

class ChangeRf {
    private ?User $user = null;
    private ?string $commentaire = '';

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user = null): void
    {
        $this->user = $user;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire = ''): void
    {
        $this->commentaire = $commentaire;
    }


}
