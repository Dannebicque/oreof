<?php

namespace App\Twig\Components;

use App\Classes\GetDpeParcours;
use App\Entity\CampagneCollecte;
use App\Entity\DpeParcours;
use App\Entity\Parcours;
use App\Enums\TypeModificationDpeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent]
final class ParcoursOuvert
{
    use DefaultActionTrait;

    #[LiveProp]
    public Parcours $parcours;

    #[LiveProp]
    public string $type = 'parcours';

    #[LiveProp]
    public DpeParcours $dpeParcours;
    #[LiveProp]
    public CampagneCollecte $campagne;

    #[LiveProp(writable: true)]
    public bool $isOuvert = false;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[PostMount]
    public function onMounted(): void
    {
        $this->dpeParcours = GetDpeParcours::getFromParcours($this->parcours);
        if ($this->dpeParcours !== null) {
            $this->campagne = $this->dpeParcours->getCampagneCollecte();
            if ($this->dpeParcours->getEtatReconduction() === TypeModificationDpeEnum::NON_OUVERTURE) {
                $this->isOuvert = false;
            } else {
                $this->isOuvert = true;
            }
        }
    }

    #[LiveAction]
    public function save(): void
    {
        $this->dpeParcours = GetDpeParcours::getFromParcours($this->parcours);
        $this->campagne = $this->dpeParcours->getCampagneCollecte();

        if ($this->isOuvert) {
            $this->dpeParcours->setEtatReconduction(TypeModificationDpeEnum::OUVERT);
        } else {
            $this->dpeParcours->setEtatReconduction(TypeModificationDpeEnum::NON_OUVERTURE);
        }

        $this->entityManager->flush();
    }

    public function getColor(): string
    {
        if ($this->dpeParcours->getEtatReconduction() === TypeModificationDpeEnum::NON_OUVERTURE) {
            return 'warning';
        }
        return 'success';

    }
}
