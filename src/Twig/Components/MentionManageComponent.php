<?php

namespace App\Twig\Components;

use App\Classes\GetDpeParcours;
use App\Classes\MentionProcess;
use App\Classes\ValidationProcess;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\TypeDiplome;
use App\Enums\EtatDemandeChangeRfEnum;
use App\Enums\EtatProcessMentionEnum;
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
        'dpe' => 'dpe',
        'soumis_conseil' => 'conseil',
        'conseil' => 'conseil',
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

    public array $process;
    public array $historiques = [];

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
        private readonly MentionProcess                $validationProcess,
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
        $historiques = $this->historiqueFormationRepository->findBy(['formation' => $this->formation], ['created' => 'ASC']);
        foreach ($historiques as $historique) {
            $this->historiques[$historique->getEtape()] = $historique;
        }
    }

    #[PostMount]
    public function postMount(): void
    {
        $this->typeDiplome = $this->formation->getTypeDiplome();
        $this->place = $this->getPlace($this->type);

        if ($this->formation !== null && $this->formation->isHasParcours() === false && $this->formation->getParcours()->count() === 1) {

            $parcours = $this->formation->getParcours()->first();
            $this->hasDemande =
                GetDpeParcours::getFromParcours($parcours)?->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_TEXTE || GetDpeParcours::getFromParcours($parcours)?->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE || GetDpeParcours::getFromParcours($parcours)?->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC;
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
        $objet = GetDpeParcours::getFromFormation($this->formation);
        if (null === $objet) {
            return 'initialisation_dpe';
        }

        return array_keys($this->getWorkflow()->getMarking($objet)->getPlaces())[0];
    }

    public function stateProcess(string $etat): EtatProcessMentionEnum
    {
        /*
         * Etat des fiches
            bleu - au moins une fiche en cours de rédaction et non validée
            orange - réserve sur au moins une fiche ou non conforme
            vert - Toutes les fiches sont validées, pas de soucis
         * Etat des parcours
            bleu - au moins un DPE parcours en cours de rédaction et non validée CFVU (en cours de process)
            orange - réserve sur au moins une fiche DPE parcours ou sans PV associé ou non conforme
            vert - Toutes les fiches DPE parcours sont validées CFVU
         * Etat formation
            bleu - au moins un DPE en cours de rédaction et non validée SES (en cours de process) - vérifier qu'il n'y a pas de modifications de textes en cours
            orange - réserves sur fiche DPE ou non conforme
            vert - fiche DPE validées SES
         * Change RF
            bleu - le RF en cours de modification et non validée CFVU (en cours de process)
            orange - réserve sur le RF ou sans PV associé
            vert - RF est validé CFVU avec PV
         * Publication
            bleu - le DPE (formation ou parcours) en cours de modification et non validée à publier (en cours de process)
            orange - réserve (est-ce utile ?)
            vert - tous les DPE parcours sont à l'état publication + formation OK
         */
        switch ($etat) {
            case 'fiche_matiere':
                return $this->getEtatFichesMatieres();
            case 'parcours':
                return $this->getEtatParcours();
            case 'formation':
                return $this->getEtatFormation();
            case 'change_rf':
                return $this->getEtatChangeRf();
            case 'publication':
                return $this->getEtatPublication();
            default:
                return EtatProcessMentionEnum::WIP;
        }
    }

    private function getEtatParcours(): EtatProcessMentionEnum
    {
        // * Etat des parcours
        //            bleu - au moins un DPE parcours en cours de rédaction et non validée CFVU (en cours de process)
        //            orange - réserve sur au moins une fiche DPE parcours ou sans PV associé ou non conforme
        //            vert - Toutes les fiches DPE parcours sont validées CFVU
        $etatsReserves = [
            'valider_reserve_conseil_cfvu',
            'reserver_parcours_rf',
            'reserver_rf',
            'reserver_dpe_composante',
            'reserver_conseil',
            'reserver_central',
            'valider_reserve_cfvu',
            'valider_reserve_conseil_cfvu',
            'valider_reserve_central_cfvu',
        ];
        list($states, $nbParcours) = $this->getEtatsParcours();

        foreach ($states as $etat => $nb) {
            if ($etat === 'autorisation_saisie' && $nb === $nbParcours) {
                return EtatProcessMentionEnum::NON_FAIT;
            }

            if (in_array($etat, $etatsReserves)) {
                return EtatProcessMentionEnum::RESERVE;
            }

            if ($etat === 'valide_cfvu' && $nb === $nbParcours) {
                return EtatProcessMentionEnum::COMPLETE;
            }
        }


        return EtatProcessMentionEnum::WIP;
    }

    private function getEtatFichesMatieres(): EtatProcessMentionEnum
    {
        //* Etat des fiches
        //            bleu - au moins une fiche en cours de rédaction et non validée
        //            orange - réserve sur au moins une fiche ou non conforme
        //            vert - Toutes les fiches sont validées, pas de soucis
        $nbFiches = 0;
        $nbFichesPubliees = 0;


        foreach ($this->formation->getParcours() as $parcours) {
            //compter le nombre de fiches matières validées
            $etatsFiche = $parcours->getEtatsFichesMatieres();
            $nbFiches += $etatsFiche->nbFiches;
            $nbFichesPubliees += $etatsFiche->nbFichesPubliees;
        }

        if ($nbFiches === 0) {
            return EtatProcessMentionEnum::NON_FAIT;
        }

        if ($nbFiches === $nbFichesPubliees) {
            return EtatProcessMentionEnum::COMPLETE;
        }

        return EtatProcessMentionEnum::WIP;
    }

    private function getEtatFormation(): EtatProcessMentionEnum
    {
        //* Etat formation
        //            bleu - au moins un DPE en cours de rédaction et non validée SES (en cours de process) - vérifier qu'il n'y a pas de modifications de textes en cours
        //            orange - réserves sur fiche DPE ou non conforme
        //            vert - fiche DPE validées SES
        return EtatProcessMentionEnum::WIP;
    }

    private function getEtatChangeRf(): EtatProcessMentionEnum
    {
        //* Change RF
        //            bleu - le RF en cours de modification et non validée CFVU (en cours de process)
        //            orange - réserve sur le RF ou sans PV associé
        //            vert - RF est validé CFVU avec PV
        foreach ($this->formation->getChangeRves() as $changeRf) {
            if ($changeRf->getEtatDemande() === EtatDemandeChangeRfEnum::EN_ATTENTE) {
                return EtatProcessMentionEnum::WIP;
            }

            if ($changeRf->getEtatDemande() === EtatDemandeChangeRfEnum::REFUSE || $changeRf->getFichierPv() === null) {
                //voir si PV passe dans l'historique
                return EtatProcessMentionEnum::RESERVE;
            }
        }

        return EtatProcessMentionEnum::COMPLETE;
    }

    private function getEtatPublication(): EtatProcessMentionEnum
    {
        // * Publication
        //            bleu - le DPE (formation ou parcours) en cours de modification et non validée à publier (en cours de process)
        //            orange - réserve (est-ce utile ?)
        //            vert - tous les DPE parcours sont à l'état publication + formation OK
        list($states, $nbParcours) = $this->getEtatsParcours();

        foreach ($states as $etat => $nb) {
            if ($etat === 'autorisation_saisie') {
                return EtatProcessMentionEnum::NON_FAIT;
            }

            if ($etat !== 'publie') {
                return EtatProcessMentionEnum::WIP;
            }

            if ($etat === 'publie' && $nb === $nbParcours) {
                //todo: vérifier formation
                return EtatProcessMentionEnum::COMPLETE;
            }
        }

        return EtatProcessMentionEnum::WIP;
    }

    /**
     * @return array
     */
    private function getEtatsParcours(): array
    {
        $states = [];
        $nbParcours = $this->formation->getParcours()->count();
        //parcours l'ensemble des parcours de la formation et regarde selon l'état
        foreach ($this->formation->getParcours() as $parcours) {
            $objet = GetDpeParcours::getFromParcours($parcours);
            if (null !== $objet) {
                $etat = array_keys($this->getWorkflow()->getMarking($objet)->getPlaces())[0];

                if (!array_key_exists($etat, $states)) {
                    $states[$etat] = 0;
                }

                $states[$etat]++;

            }
        }

        ksort($states);
        return array($states, $nbParcours);
    }
}
