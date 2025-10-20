<?php

namespace App\Command;

use App\Entity\AnneeUniversitaire;
use App\Entity\CampagneCollecte;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:duplicate-for-new-annee',
    description: "Duplique les données d'une année universitaire, pour créer la suivante.",
)]
class DuplicateForNewAnneeCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private array $initialisationErrorValue = [];

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                name: 'annee-source',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Identifiant (PK) de la campagne de collecte à dupliquer.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);

        $anneeSource = $input->getOption('annee-source');

        // Sélection de l'année à dupliquer
        if(isset($anneeSource) === false) {
            $io->error("Il faut définir l'année à copier (PK : Campagne Collecte) !");
            return Command::INVALID;
        }

        $campagneCollecteSource = $this->entityManager
            ->getRepository(CampagneCollecte::class)
            ->findOneById($anneeSource);

        // Vérification si la campagne de collecte sélectionnée
        // existe en base de données
        if(!$campagneCollecteSource) {
            $io->error("Aucune campagne de collecte n'a été trouvée pour cet identifiant ({$anneeSource}).");
            return Command::INVALID;
        }

        $promptLibelle = $this->getInitialisationLibelleArray();

        $io->writeln("Initialisation de la nouvelle année universitaire (BD) à créer...");
        $newAnneeUniversitaire = new AnneeUniversitaire();
        $libelleNewAnnee = $io->ask($promptLibelle['libelleNewAnnee']['askPrompt']);
        $anneeNewAnnee = $io->ask($promptLibelle['anneeNewAnnee']['askPrompt']);

        $io->writeln("Initialisation de la nouvelle campagne de collecte à créer (BD)...");
        $newCampagneCollecte = new CampagneCollecte();
        $libelleNewCampagne = $io->ask($promptLibelle['libelleNewCampagne']['askPrompt']);
        $anneeNewCampagne = $io->ask($promptLibelle['anneeNewCampagne']['askPrompt']);
        $dateOuvertureNewCampagne = $io->ask($promptLibelle['dateOuvertureNewCampagne']['askPrompt']);
        $dateClotureNewCampagne = $io->ask($promptLibelle['dateClotureNewCampagne']['askPrompt']);
        $dateTransmissionSesNewCampagne = $io->ask($promptLibelle['dateTransmissionSesNewCampagne']['askPrompt']);
        $dateCfvuNewCampagne = $io->ask($promptLibelle['dateCfvuNewCampagne']['askPrompt']);
        $datePublicationNewCampagne = $io->ask($promptLibelle['datePublicationNewCampagne']['askPrompt']);

        // Liens entre les données saisies dans la commande,
        // les libellés, messages d'erreurs, et les futurs objets
        $testInitialisationStructure = [
            [
                'type' => 'doubleYear',
                'libelle' => 'libelleNewAnnee',
                'value' => $libelleNewAnnee
            ],
            [
                'type' => 'year',
                'libelle' => 'anneeNewAnnee',
                'value' => $anneeNewAnnee
            ],
            [
                'type' => 'doubleYear',
                'libelle' => 'libelleNewCampagne',
                'value' => $libelleNewCampagne
            ],
            [
                'type' => 'year',
                'libelle' => 'anneeNewCampagne',
                'value' => $anneeNewCampagne
            ],
            [
                'type' => 'fullDate',
                'libelle' => 'dateOuvertureNewCampagne',
                'value' => $dateOuvertureNewCampagne
            ],
            [
                'type' => 'fullDate',
                'libelle' => 'dateClotureNewCampagne',
                'value' => $dateClotureNewCampagne
            ],
            [
                'type' => 'fullDate',
                'libelle' => 'dateTransmissionSesNewCampagne',
                'value' => $dateTransmissionSesNewCampagne
            ],
            [
                'type' => 'fullDate',
                'libelle' => 'dateCfvuNewCampagne',
                'value' => $dateCfvuNewCampagne
            ],
            [
                'type' => 'fullDate',
                'libelle' => 'datePublicationNewCampagne',
                'value' => $datePublicationNewCampagne
            ],
        ];

        $this->testInitialisationInput($testInitialisationStructure);

        // Vérification des formats des données, pour alimenter les objets ORM.
        if(count($this->initialisationErrorValue) > 0){
            foreach($this->initialisationErrorValue as $errorToDisplay) {
                $errorTxt = $promptLibelle[$errorToDisplay['name']]['errorMessage'] . " ({$errorToDisplay['value']}) ";
                $io->writeln($errorTxt);
            }

            $io->warning("Certaines données rentrées sont invalides !");
            return Command::INVALID;
        }

        // S'il n'y a pas d'erreur, on poursuit en alimentant les objets
        // créés, et on les enregistre en base de données.

        $io->success("La commande s'est exécutée correctement !");

        return Command::SUCCESS;
    }

    private function checkYear(string $yearToCheck) {
        return preg_match('/^[0-9]{4}$/', $yearToCheck) === 1;
    }

    private function checkDoubleYear(string $doubleYearToCheck) {
        return preg_match('/^[0-9]{4}-[0-9]{4}$/', $doubleYearToCheck) === 1;
    }

    private function checkFullDate(string $dateToCheck) {
        return preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $dateToCheck) === 1
            && DateTime::createFromFormat('Y-m-d', $dateToCheck) !== false;
    }



    private function getInitialisationLibelleArray() {
        return [
            'libelleNewAnnee' => [
                    'askPrompt' => "Libellé de la nouvelle année universitaire [YYYY-YYYY] : ",
                    'errorMessage' => "Libellé de la nouvelle année universitaire invalide."
                ], 
            'anneeNewAnnee' => [
                    'askPrompt' => "Année de la nouvelle année universitaire [YYYY] : ",
                    'errorMessage' => "Année de la nouvelle année universitaire invalide."
                ], 
            'libelleNewCampagne' => [
                    'askPrompt' => "Libellé de la nouvelle Campagne de Collecte [YYYY-YYYY] : ",
                    'errorMessage' => "Libellé de la nouvelle Campagne de Collecte invalide."
                ],
            'anneeNewCampagne' => [
                    'askPrompt' => "Année de la nouvelle Campagne de Collecte [YYYY]",
                    'errorMessage' => "Année de la nouvelle Campagne de Collecte invalide."
                ], 
            'dateOuvertureNewCampagne' => [
                    'askPrompt' => "Date d'ouverture du DPE pour la nouvelle Campagne de Collecte [YYYY-MM-DD] : ",
                    'errorMessage' => "Date d'ouverture du DPE invalide."
                ], 
            'dateClotureNewCampagne' => [
                    'askPrompt' => "Date de clôture du DPE pour la nouvelle Campagne de Collecte [YYYY-MM-DD] : ",
                    'errorMessage' => "Date de clôture du DPE invalide."
                ],
            'dateTransmissionSesNewCampagne' => [
                    'askPrompt' => "Date de transmission aux SES pour la nouvelle Campagne de Collecte [YYYY-MM-DD] : ",
                    'errorMessage' => "Date de transmission aux SES invalide."
                ],
            'dateCfvuNewCampagne' => [
                    'askPrompt' => "Date de la CFVU pour la nouvelle Campagne de Collecte [YYYY-MM-DD] : ",
                    'errorMessage' => "Date de la CFVU invalide."
                ],
            'datePublicationNewCampagne' => [
                    'askPrompt' => "Date de publication pour la nouvelle Campagne de Collecte [YYYY-MM-DD] : ",
                    'errorMessage' => "Date de publication invalide."
                ],
        ];
    }

    private function testInitialisationInput(array $inputArray) {
        foreach($inputArray as $inputData){
            switch($inputData['type']){
                case 'year':
                    if($this->checkYear($inputData['value']) === false) {
                        $this->initialisationErrorValue[] = [
                            'name' => $inputData['libelle'],
                            'value' => $inputData['value']
                        ];
                    }
                    break;
                case 'doubleYear': 
                    if($this->checkDoubleYear($inputData['value']) === false) {
                        $this->initialisationErrorValue[] = [
                            'name' => $inputData['libelle'], 
                            'value' => $inputData['value']
                        ];
                    }
                    break;
                case 'fullDate': 
                    if($this->checkFullDate($inputData['value']) === false) {
                        $this->initialisationErrorValue[] = [
                            'name' => $inputData['libelle'],
                            'value' => $inputData['value']
                        ];
                    }
                    break;
            }
        }
    }
}