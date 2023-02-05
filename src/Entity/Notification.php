<?php

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Notification
{
    use LifeCycleTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    private ?User $destinataire = null;

    #[ORM\Column(length: 150)]
    private ?string $codeNotification = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $options = null;

    #[ORM\Column]
    private ?bool $lu = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDestinataire(): ?User
    {
        return $this->destinataire;
    }

    public function setDestinataire(?User $destinataire): self
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    public function getCodeNotification(): ?string
    {
        return $this->codeNotification;
    }

    public function setCodeNotification(string $codeNotification): self
    {
        $this->codeNotification = $codeNotification;

        return $this;
    }

    public function getOptions(): ?string
    {
        return $this->options;
    }

    public function setOptions(?string $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function isLu(): ?bool
    {
        return $this->lu;
    }

    public function setLu(bool $lu): self
    {
        $this->lu = $lu;

        return $this;
    }
}
