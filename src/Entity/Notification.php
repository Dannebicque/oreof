<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/Notification.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/02/2023 11:44
 */

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Repository\NotificationRepository;
use DateTimeImmutable;
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

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $body = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $payload = null; // ex: lien vers workflow, transition, id objet...

    #[ORM\Column(type: 'boolean')]
    private bool $isRead = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isRead = false;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function setPayload(?array $payload): self
    {
        $this->payload = $payload;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function markAsRead(): self
    {
        $this->isRead = true;

        if ($this->requiresAck()) {
            $payload = $this->payload ?? [];
            $payload['ackAt'] = (new DateTimeImmutable())->format(DATE_ATOM);
            $this->payload = $payload;
        }

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

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
        return $this->payload['codeNotification'] ?? $this->title ?? null;
    }

    public function setCodeNotification(string $codeNotification): self
    {
        $payload = $this->payload ?? [];
        $payload['codeNotification'] = $codeNotification;
        $this->payload = $payload;

        if (!isset($this->title) || trim($this->title) === '') {
            $this->title = $codeNotification;
        }

        return $this;
    }

    public function getOptions(): ?string
    {
        return $this->payload['options'] ?? null;
    }

    public function setOptions(?string $options): self
    {
        $payload = $this->payload ?? [];
        $payload['options'] = $options;
        $this->payload = $payload;

        return $this;
    }

    public function isLu(): ?bool
    {
        return $this->isRead;
    }

    public function setLu(bool $lu): self
    {
        if ($lu === true) {
            return $this->markAsRead();
        }

        $this->isRead = false;

        if ($this->requiresAck()) {
            $payload = $this->payload ?? [];
            $payload['ackAt'] = null;
            $this->payload = $payload;
        }

        return $this;
    }

    public function isMutualisationNotification(): bool
    {
        return ($this->payload['category'] ?? null) === 'mutualisation_update';
    }

    public function requiresAck(): bool
    {
        return ($this->payload['mustAck'] ?? false) === true;
    }

    public function isAckPending(): bool
    {
        if (!$this->isMutualisationNotification() || !$this->requiresAck()) {
            return false;
        }

        return $this->isRead === false && empty($this->payload['ackAt']);
    }

    public function getMutualisationParcoursId(): ?int
    {
        $id = $this->payload['parcoursId'] ?? null;

        return is_numeric($id) ? (int)$id : null;
    }

    public function isPendingForParcours(?int $parcoursId): bool
    {
        if (!$this->isAckPending()) {
            return false;
        }

        if ($parcoursId === null) {
            return true;
        }

        return $this->getMutualisationParcoursId() === $parcoursId;
    }
}
