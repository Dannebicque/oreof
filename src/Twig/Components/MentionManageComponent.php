<?php

namespace App\Twig\Components;

use App\Classes\GetDpeParcours;
use App\Classes\ValidationProcess;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\TypeDiplome;
use App\Enums\TypeModificationDpeEnum;
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

#[AsLiveComponent('mention_manage')]
final class MentionManageComponent extends AbstractController
{
    public const TAB = [
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
        'ses' => 'ses',
        'soumis_vp' => 'vp',
        'cfvu' => 'cfvu',
        'soumis_cfvu' => 'cfvu',
        'refuse_definitif_cfvu' => 'cfvu',
        'valide_a_publier' => 'cfvu',
        'publie' => 'publication',
        'valide_pour_publication' => 'publication',
        'soumis_conseil_reserve' => 'cfvu',
    ];

    public const TAB_PROCESS = [
        'ouverture_ses_ss_cfvu' => 0,
        'cloture_ses_ss_cfvu' => 0,
        'parcours' => 0,
        'parcours_rf' => 1,
        'formation' => 2,
        'dpe' => 3,
        'conseil' => 4,
        'ses' => 5,
        'soumis_central' => 5,
        'cfvu' => 6,
        'publication' => 7
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
    public ?TypeDiplome $typeDiplome;

    public string $etape = '';
    public string $place = '';
    public bool $hasDemande = true;

    #[LiveProp]
    public string $type = 'formation';

    #[LiveProp(writable: true)]
    public string $event = 'none';

    public function __construct(
        private readonly HistoriqueFormationRepository $historiqueFormationRepository,
        private readonly HistoriqueParcoursRepository  $historiqueParcoursRepository,
        private readonly ValidationProcess             $validationProcess,
        #[Target('dpeParcours')]
        private readonly WorkflowInterface             $dpeParcoursWorkflow,
    ) {
        $this->process = $this->validationProcess->getProcess();
    }

    #[LiveListener('mention_manage:valide')]
    public function valide(): void
    {
        $this->place = $this->getPlace($this->type);
        $this->etape = self::TAB[$this->place] ?? $this->type;
        $this->getHistorique();
        $this->event = 'valide';

        $this->redirige();
    }

    public function redirige(): Response
    {
        // si niveau parcours, formation ou composante, une fois validé => On redirige vers le show.
        if ($this->type === 'parcours') {
            return $this->redirectToRoute('app_parcours_show', [
                'id' => $this->parcours->getId()
            ]); //uniquement si RF ou DPE
        }

        return $this->redirectToRoute('app_formation_show', [
            'slug' => $this->formation->getSlug()
        ]); //uniquement si RF ou DPE
    }

    #[LiveListener('mention_manage:edit')]
    public function edit(): void
    {
        $this->place = $this->getPlace($this->type);
        $this->etape = self::TAB[$this->place] ?? $this->type;
        $this->event = 'edit';
    }

    #[LiveListener('mention_manage:refuse')]
    public function refuse(): void
    {
        $this->place = $this->getPlace($this->type);
        $this->etape = self::TAB[$this->place] ?? $this->type;
        $this->getHistorique();
        $this->event = 'refuse';
    }

    #[LiveListener('mention_manage:reserve')]
    public function reserve(): void
    {
        $this->place = $this->getPlace($this->type);
        $this->etape = self::TAB[$this->place] ?? $this->type;
        $this->getHistorique();
        $this->event = 'reserve';
    }

    private function getHistorique(): void
    {
        if ($this->type === 'formation') {
            $historiques = $this->historiqueFormationRepository->findBy(['formation' => $this->formation], ['created' => 'ASC']);
            foreach ($historiques as $historique) {
                $this->historiques[$historique->getEtape()] = $historique;
            }
        } elseif ($this->type === 'parcours') {
            $historiques = $this->historiqueParcoursRepository->findBy(['parcours' => $this->parcours], ['created' => 'ASC']);

            foreach ($historiques as $historique) {
                if (self::TAB_PROCESS[$historique->getEtape()] < self::TAB_PROCESS[self::TAB[$this->etape]]) {
                    $this->historiques[$historique->getEtape()] = $historique;
                }
            }
            $historiques = $this->historiqueFormationRepository->findBy(['formation' => $this->parcours->getFormation()], ['created' => 'ASC']);
            foreach ($historiques as $historique) {
                if (self::TAB_PROCESS[$historique->getEtape()] < self::TAB_PROCESS[self::TAB[$this->etape]]) {
                    $this->historiques[$historique->getEtape()] = $historique;
                }
            }

        }

    }

    #[PostMount]
    public function postMount(): void
    {
        if ($this->type === 'formation') {
            $this->typeDiplome = $this->formation->getTypeDiplome();
            $this->place = $this->getPlace($this->type);

            if ($this->formation !== null && $this->formation->isHasParcours() === false) {
                $parcours = $this->formation->getParcours()->first();
                $this->hasDemande =
                    GetDpeParcours::getFromParcours($parcours)?->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_TEXTE || GetDpeParcours::getFromParcours($parcours)?->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE || GetDpeParcours::getFromParcours($parcours)?->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC;
            }
        } elseif ($this->type === 'parcours' && $this->parcours !== null) {
            $this->typeDiplome = $this->parcours?->getFormation()?->getTypeDiplome();
            $this->formation = $this->parcours?->getFormation();
            $this->place = $this->getPlace($this->type);
            $this->hasDemande =
                GetDpeParcours::getFromParcours($this->parcours)?->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_TEXTE || GetDpeParcours::getFromParcours($this->parcours)?->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE || GetDpeParcours::getFromParcours($this->parcours)?->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC;
        } elseif ($this->type === 'ficheMatiere') {
            $this->typeDiplome = $this->ficheMatiere->getParcours()?->getFormation()?->getTypeDiplome();
            $this->parcours = $this->ficheMatiere->getParcours();
            $this->formation = $this->parcours->getFormation();
        }
        $this->etape = self::TAB[$this->place] ?? $this->type;
        $this->getHistorique();

        // dépend du type et de l'étape...

    }

    public function dateHistorique(string $transition): string
    {
        if (array_key_exists($transition, $this->historiques)) {
            return $this->historiques[$transition]->getDate() !== null ? $this->historiques[$transition]->getDate()->format('d/m/Y') : $this->historiques[$transition]->getCreated()->format('d/m/Y');
        }

        return '- à venir -';
    }

    private function getWorkflow(): WorkflowInterface
    {
        //un seul workflow celui du DpeParcours
        return $this->dpeParcoursWorkflow;
    }

    private function getPlace(string $type): string
    {
        $objet = $type === 'formation' || $this->parcours === null ? GetDpeParcours::getFromFormation($this->formation) : GetDpeParcours::getFromParcours($this->parcours);
        if (null === $objet) {
            return 'initialisation_dpe';
        }

        return array_keys($this->getWorkflow()->getMarking($objet)->getPlaces())[0];
    }
}
