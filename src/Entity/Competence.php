<?php

namespace App\Entity;

use App\Repository\CompetenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetenceRepository::class)]
class Competence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'competences')]
    private ?BlocCompetence $blocCompetence;

    #[ORM\Column(length: 10)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToMany(targetEntity: ElementConstitutif::class, mappedBy: 'competences')]
    private Collection $elementConstitutifs;

    #[ORM\Column]
    private ?int $ordre = null;


    public function __construct(BlocCompetence $blocCompetence)
    {
        $this->blocCompetence = $blocCompetence;
        $this->elementConstitutifs = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBlocCompetence(): ?BlocCompetence
    {
        return $this->blocCompetence;
    }

    public function setBlocCompetence(?BlocCompetence $blocCompetence): self
    {
        $this->blocCompetence = $blocCompetence;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
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

    /**
     * @return Collection<int, ElementConstitutif>
     */
    public function getElementConstitutifs(): Collection
    {
        return $this->elementConstitutifs;
    }

    public function addElementConstitutif(ElementConstitutif $elementConstitutif): self
    {
        if (!$this->elementConstitutifs->contains($elementConstitutif)) {
            $this->elementConstitutifs->add($elementConstitutif);
            $elementConstitutif->addCompetence($this);
        }

        return $this;
    }

    public function removeElementConstitutif(ElementConstitutif $elementConstitutif): self
    {
        if ($this->elementConstitutifs->removeElement($elementConstitutif)) {
            $elementConstitutif->removeCompetence($this);
        }

        return $this;
    }

    public function display(): string
    {
        return $this->getCode() . ' - '. $this->getLibelle();
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function genereCode()
    {
        $this->setCode(chr($this->getBlocCompetence()?->getOrdre()+64)  . $this->getOrdre());
    }
}
