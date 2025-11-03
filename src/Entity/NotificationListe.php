<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/NotificationListe.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/01/2023 21:04
 */

namespace App\Entity;

use App\Repository\NotificationListeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationListeRepository::class)]
class NotificationListe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notificationListes')]
    private ?Profil $profil = null;

    #[ORM\Column(length: 40)]
    private ?string $workflow = null;

    #[ORM\Column(length: 255)]
    private ?string $step = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfil(): ?Profil
    {
        return $this->profil;
    }

    public function setProfil(?Profil $profil): static
    {
        $this->profil = $profil;

        return $this;
    }

    public function getWorkflow(): ?string
    {
        return $this->workflow;
    }

    public function setWorkflow(string $workflow): static
    {
        $this->workflow = $workflow;

        return $this;
    }

    public function getStep(): ?string
    {
        return $this->step;
    }

    public function setStep(string $step): static
    {
        $this->step = $step;

        return $this;
    }


}
