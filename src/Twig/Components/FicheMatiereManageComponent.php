<?php

namespace App\Twig\Components;

use App\Classes\ValidationProcess;
use App\Classes\ValidationProcessFicheMatiere;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\TypeDiplome;
use App\Repository\HistoriqueFicheMatiereRepository;
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
        'en_cours_redaction' => 'fiche_matiere',
        'soumis_central' => 'soumis_central',
        'valide_pour_publication' => 'valide_pour_publication',
        'publie' => 'publie',
    ];
    use DefaultActionTrait;

    public array $process;
    public array $historiques = [];

    #[LiveProp(writable: true)]
    public ?FicheMatiere $ficheMatiere = null;

    public ?string $etape = '';

    #[LiveProp(writable: true)]
    public string $event = 'none';

    public function __construct(
        private HistoriqueFicheMatiereRepository $historiqueFicheMatiereRepository,
        private ValidationProcessFicheMatiere             $validationProcess,
        #[Target('fiche')]
        private WorkflowInterface             $ficheMatiereWorkflow,
    ) {
        $this->process = $this->validationProcess->getProcess();
    }

    #[LiveListener('mention_manage:valide')]
    public function valide(): void
    {
        $place = $this->getPlace();
        $this->etape = self::TAB[$place];
        $this->getHistorique();
        $this->event = 'valide';
    }

    private function getHistorique(): void
    {
            $historiques = $this->historiqueFicheMatiereRepository->findBy(['ficheMatiere' => $this->ficheMatiere], ['created' => 'ASC']);
            foreach ($historiques as $historique) {
                $this->historiques[$historique->getEtape()] = $historique;
            }
    }

    #[LiveListener('mention_manage:edit')]
    public function edit(): void
    {
        $place = $this->getPlace();
        $this->etape = self::TAB[$place];
        $this->event = 'edit';
    }

    #[LiveListener('mention_manage:refuse')]
    public function refuse(): void
    {
        $place = $this->getPlace();
        $this->etape = self::TAB[$place];
        $this->event = 'refuse';
    }

    #[LiveListener('mention_manage:reserve')]
    public function reserve(): void
    {
        $place = $this->getPlace();
        $this->etape = self::TAB[$place];
        $this->event = 'reserve';
    }

    #[PostMount]
    public function postMount(): void
    {
        $this->typeDiplome = $this->ficheMatiere->getParcours()?->getFormation()?->getTypeDiplome();
        $this->parcours = $this->ficheMatiere->getParcours();
        $this->formation = $this->parcours->getFormation();
        $this->getHistorique();

        // dépend du type et de l'étape...
        $place = $this->getPlace();
       $this->etape = self::TAB[$place] ;
    }


    private function getPlace()
    {
        return array_keys($this->ficheMatiereWorkflow->getMarking($this->ficheMatiere)->getPlaces())[0];
    }
}
