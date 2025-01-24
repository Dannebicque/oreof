<?php

namespace App\Twig\Components;

use App\Classes\ValidationProcessChangeRf;
use App\Entity\ChangeRf;
use App\Entity\Formation;
use App\Repository\HistoriqueFormationRepository;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent]
final class ChangeRfState
{
    use DefaultActionTrait;

    public Formation $formation;
    public array $demandes = [];
    public array $process;
    public string $place = '';
    public array $historiques = [];

    public const TAB_PROCESS = [
        'changeRf.soumis_conseil' => 0,
        'changeRf.soumis_ses' => 1,
        'changeRf.soumis_cfvu' => 2,
        'changeRf.attente_pv' => 2,
    ];

    public function __construct(
        private readonly HistoriqueFormationRepository $historiqueFormationRepository,
        private readonly WorkflowInterface $changeRfWorkflow,
        private readonly ValidationProcessChangeRf $validationProcessChangeRf
    ) {
        $this->process = $this->validationProcessChangeRf->getProcess();
    }

    #[PostMount]
    public function getDemandes(): void
    {
        foreach ($this->formation->getChangeRves() as $changeRf) {
            if (!$this->changeRfWorkflow->getMarkingStore()->getMarking($changeRf)->has('effectuee')) {
                $this->demandes[] = $changeRf;
            }
        }
    }

    public function getPlace(ChangeRf $changeRf): string
    {
        return array_keys($this->changeRfWorkflow->getMarking($changeRf)->getPlaces())[0];
        ;
    }

    public function getHistoriques(ChangeRf $changeRf): array
    {
        $historiques = $this->historiqueFormationRepository->findBy(['changeRf' => $changeRf], ['created' => 'ASC']);

        foreach ($historiques as $historique) {
            if (str_starts_with($historique->getEtape(), 'changeRf.')) {
                if (self::TAB_PROCESS[$historique->getEtape()] < self::TAB_PROCESS['changeRf.'.$this->getPlace($changeRf)]) {
                    $this->historiques[$historique->getEtape()] = $historique;
                }
            }
        }
        return $this->historiques;
    }
}
