<?php

namespace App\Twig\Components;

use App\Entity\Formation;
use App\Entity\Parcours;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent]
final class CommentaireAdmin
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?Parcours $parcours = null;

    #[LiveProp]
    public ?Formation $formation = null;

    #[LiveProp(writable: true)]
    public ?string $commentaire = null;

    #[LiveProp(writable: true)]
    public bool $isEditable = false;

    public function __construct(
        protected EntityManagerInterface $entityManager,
    )
    {
    }

    #[PostMount]
    public function onMount(): void
    {

        if ($this->parcours !== null) {
            $this->commentaire = $this->parcours->getCommentaire() ?? '';
        } elseif ($this->formation !== null) {
            $this->commentaire = $this->formation->getCommentaire() ?? '';
        } else {
            throw new \InvalidArgumentException('Either parcours or formation must be provided.');
        }
    }

    #[LiveAction]
    public function edit(): void
    {
        $this->isEditable = true;
    }

    #[LiveAction]
    public function sauvegarde(): void
    {
        $this->isEditable = false;
        if ($this->parcours !== null) {
            $this->parcours->setCommentaire($this->commentaire);
        } else {
            $this->formation->setCommentaire($this->commentaire);
        }
        $this->entityManager->flush();
    }
}
