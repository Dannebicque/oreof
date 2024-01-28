<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Message/ExportMessage.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/07/2023 10:58
 */

namespace App\Message;

use App\Entity\CampagneCollecte;
use DateTimeInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Export
{
    public function __construct(
        private readonly int $userId,
        private readonly string $typeDocument,
        private readonly array $formations,
        private readonly ?int $anneeUniversitaireId = null,
        private readonly ?DateTimeInterface $date = null)
    {
    }
    public  function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public  function getUser(): int
    {
        return $this->userId;
    }
    public  function getTypeDocument(): string
    {
        return $this->typeDocument;
    }
    public  function getFormations(): array
    {
        return $this->formations;
    }
    public  function getAnnee(): ?int
    {
        return $this->anneeUniversitaireId;
    }
}
