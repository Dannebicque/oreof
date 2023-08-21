<?php

namespace App\Twig\Components;

use App\Classes\ValidationProcess;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\TypeDiplome;
use App\Repository\HistoriqueFormationRepository;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent('mention_manage')]
final class MentionManageComponent
{
    const TAB = [
        'initialisation_dpe' => 'formation',
        'autorisation_saisie' => null,
        'en_cours_redaction' => null,
        'soumis_parcours' => 'formation',
        'soumis_rf' => 'formation',
        'soumis_dpe_composante' => 'dpe',
        'refuse_rf' => 'formation',
        'refuse_dpe_composante' => 'dpe',
        'soumis_conseil' => 'conseil',
        'refuse_conseil' => 'ses',
        'refuse_central' => 'ses',
        'soumis_central' => 'ses',
        'soumis_vp' => 'vp',
        'soumis_cfvu' => 'cfvu',
        'refuse_definitif_cfvu' => 'cfvu',
        'valide_a_publier' => 'cfvu',
        'publie' => 'publication',
        'valide_pour_publication' => 'publication',
        'soumis_conseil_reserve' => 'cfvu',

    ];
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?Formation $formation = null;

    #[LiveProp]
    public ?Parcours $parcours = null;

    public array $process;
    public array $historiques = [];

    #[LiveProp]
    public ?FicheMatiere $ficheMatiere = null;

    #[LiveProp]
    public TypeDiplome $typeDiplome;

    public string $etape = '';

    #[LiveProp]
    public string $type = 'formation';

    #[LiveProp(writable: true)]
    public string $event = 'none';

    public function __construct(
        private HistoriqueFormationRepository    $historiqueFormationRepository,
        private ValidationProcess                $validationProcess,
        #[Target('dpe')]
        private WorkflowInterface                $dpeWorkflow,
        #[Target('parcours')]
        private WorkflowInterface                $parcoursWorkflow,
    ) {
        $this->process = $this->validationProcess->getProcess();
    }

    #[LiveListener('mention_manage:valide')]
    public function valide()
    {
        $this->typeDiplome = $this->parcours?->getFormation()->getTypeDiplome();
        $this->formation = $this->parcours?->getFormation();
        $place = array_keys($this->dpeWorkflow->getMarking($this->formation)->getPlaces())[0];
        $this->etape = self::TAB[$place] ?? $this->type;
        $this->event = 'valide';
    }

    #[LiveListener('mention_manage:edit')]
    public function edit()
    {
        $this->typeDiplome = $this->parcours?->getFormation()->getTypeDiplome();
        $this->formation = $this->parcours?->getFormation();
        $place = array_keys($this->dpeWorkflow->getMarking($this->formation)->getPlaces())[0];
        $this->etape = self::TAB[$place] ?? $this->type;
        $this->event = 'edit';
    }

    #[LiveListener('mention_manage:refuse')]
    public function refuse()
    {
        $this->typeDiplome = $this->parcours?->getFormation()->getTypeDiplome();
        $this->formation = $this->parcours?->getFormation();
        $place = array_keys($this->dpeWorkflow->getMarking($this->formation)->getPlaces())[0];
        $this->etape = self::TAB[$place] ?? $this->type;
        $this->event = 'valide';
    }

    #[PostMount]
    public function postMount(): void
    {
        if ($this->type === 'formation') {
            $this->typeDiplome = $this->formation->getTypeDiplome();
            $place = array_keys($this->dpeWorkflow->getMarking($this->formation)->getPlaces())[0];
        } elseif ($this->type === 'parcours') {
            $this->typeDiplome = $this->parcours?->getFormation()->getTypeDiplome();
            $this->formation = $this->parcours?->getFormation();
            $place = array_keys($this->parcoursWorkflow->getMarking($this->parcours)->getPlaces())[0];
        } elseif ($this->type === 'ficheMatiere') {
            $this->typeDiplome = $this->ficheMatiere->getParcours()->getFormation()->getTypeDiplome();
            $this->parcours = $this->ficheMatiere->getParcours();
            $this->formation = $this->parcours->getFormation();

        }

        $historiques = $this->historiqueFormationRepository->findBy(['formation' => $this->formation], ['created' => 'DESC']);
        foreach ($historiques as $historique) {
            $this->historiques[$historique->getEtape()] = $historique->getCreated();
        }

        // dépend du type et de l'étape...
        $this->etape = self::TAB[$place] ?? $this->type;
    }

    public function dateHistorique(string $transition): string
    {

        if (array_key_exists($transition, $this->historiques)) {
            return $this->historiques[$transition]->format('d/m/Y');
        }

        return '- à venir -';
    }
}
