<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/Mention.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 22/02/2023 10:47
 */

namespace App\Entity;

use App\Repository\MentionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MentionRepository::class)]
class Mention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $sigle = null;

//    #[ORM\Column(length: 255)]
//    private ?string $typeDiplome = null;

    #[ORM\ManyToOne(inversedBy: 'mentions')]
    private ?Domaine $domaine = null;

    #[ORM\ManyToOne(inversedBy: 'mentions')]
    private ?TypeDiplome $typeDiplome = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getSigle(): ?string
    {
        return $this->sigle;
    }

    public function setSigle(?string $sigle): self
    {
        $this->sigle = $sigle;

        return $this;
    }

//    public function getTypeDiplome(): ?string
//    {
//        return $this->typeDiplome;
//    }
//
//    public function setTypeDiplome(?string $typeDiplome): self
//    {
//        $this->typeDiplome = $typeDiplome;
//
//        return $this;
//    }

    public function getDomaine(): ?Domaine
    {
        return $this->domaine;
    }

    public function setDomaine(?Domaine $domaine): self
    {
        $this->domaine = $domaine;

        return $this;
    }

    public function getTypeDiplome(): ?TypeDiplome
    {
        return $this->typeDiplome;
    }

    public function setTypeDiplome(?TypeDiplome $typeDiplome): self
    {
        $this->typeDiplome = $typeDiplome;

        return $this;
    }
}
