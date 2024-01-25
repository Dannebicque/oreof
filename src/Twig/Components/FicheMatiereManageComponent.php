<?php

namespace App\Twig\Components;

use App\Classes\ValidationProcess;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\TypeDiplome;
use App\Repository\HistoriqueFormationRepository;
use App\Repository\HistoriqueParcoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent('fiche_matiere_manage')]
final class FicheMatiereManageComponent extends AbstractController
{
    const TAB = [
        'initialisation_dpe' => 'formation',
        'autorisation_saisie' => null,
        'en_cours_redaction' => null,
        'soumis_parcours' => 'parcours_rf',
        'valide_parcours_rf' => 'formation',
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

    public array $process;
    public array $historiques = [];

    #[LiveProp(writable: true)]
    public ?FicheMatiere $ficheMatiere = null;

    public string $etape = '';

    #[LiveProp(writable: true)]
    public string $event = 'none';

    public function __construct(
        private HistoriqueFormationRepository $historiqueFormationRepository,
        private HistoriqueParcoursRepository  $historiqueParcoursRepository,
        private ValidationProcess             $validationProcess,
        #[Target('dpe')]
        private WorkflowInterface             $dpeWorkflow,
        #[Target('parcours')]
        private WorkflowInterface             $parcoursWorkflow,
    ) {
        $this->process = $this->validationProcess->getProcess();
    }

    #[LiveListener('mention_manage:valide')]
    public function valide(): void
    {
//        $place = $this->getPlace($this->type);
//        $this->etape = self::TAB[$place] ?? $this->type;
//        $this->getHistorique();
        $this->event = 'valide';
    }

    #[LiveListener('mention_manage:edit')]
    public function edit(): void
    {
        $place = $this->getPlace($this->type);
        $this->etape = self::TAB[$place] ?? $this->type;
        $this->event = 'edit';
    }

    #[LiveListener('mention_manage:refuse')]
    public function refuse(): void
    {
//        $place = $this->getPlace($this->type);
//        $this->etape = self::TAB[$place] ?? $this->type;
        $this->event = 'refuse';
    }

    #[LiveListener('mention_manage:reserve')]
    public function reserve(): void
    {
//        $place = $this->getPlace($this->type);
//        $this->etape = self::TAB[$place] ?? $this->type;
        $this->event = 'reserve';
    }

    #[PostMount]
    public function postMount(): void
    {
        $this->typeDiplome = $this->ficheMatiere->getParcours()?->getFormation()?->getTypeDiplome();
        $this->parcours = $this->ficheMatiere->getParcours();
        $this->formation = $this->parcours->getFormation();

        // dépend du type et de l'étape...
       // $this->etape = self::TAB[$place] ?? $this->type;
    }

    private function getWorkflow(string $type): WorkflowInterface
    {
        if ($type === 'formation') {
            return $this->dpeWorkflow;
        }

        if ($type === 'parcours') {
            return $this->parcoursWorkflow;
        }
    }

    private function getPlace(string $type)
    {
        $objet = $type === 'formation' ? $this->formation : $this->parcours;
        return array_keys($this->getWorkflow($type)->getMarking($objet)->getPlaces())[0];
    }
}
