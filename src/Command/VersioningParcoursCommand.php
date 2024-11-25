<?php

namespace App\Command;

use App\Classes\GetHistorique;
use App\Entity\CampagneCollecte;
use App\Entity\DpeParcours;
use App\Entity\Parcours;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use DateTimeImmutable;
use App\Entity\ParcoursVersioning;
use App\Service\VersioningParcours;
use App\TypeDiplome\TypeDiplomeRegistry;
use DateTime;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[AsCommand(
    name: 'app:versioning-parcours',
    description: "Sauvegarde d'un ou plusieurs parcours au format JSON",
)]
class VersioningParcoursCommand extends Command
{

    private EntityManagerInterface $entityManager;
    private Filesystem $filesystem;
    private VersioningParcours $versioningParcours;
    private GetHistorique $getHistorique;

    public function __construct(
        EntityManagerInterface $entityManager,
        Filesystem $filesystem,
        VersioningParcours $versioningParcours,
        GetHistorique $getHistorique
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
        $this->versioningParcours = $versioningParcours;
        $this->getHistorique = $getHistorique;
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'dpe-full-valid-database', 
            mode: InputOption::VALUE_NONE,
            description: 'Sauvegarde tous les parcours de la base de données en JSON'
        )
        ->addOption(
            name: 'dpe-today-cfvu-valid',
            mode: InputOption::VALUE_NONE,
            description: "Sauvegarde tous les parcours qui sont validés CFVU (dernier état à \"valide_a_publier\"). et la date est aujourd'hui",
        )
        ->addOption(
            name: 'with-skip-option',
            mode: InputOption::VALUE_NONE,
            description: "Ne sauvegarde pas une version de parcours, si la dernière version est validée CFVU"
        )->addOption(
            name: 'single-parcours',
            mode: InputOption::VALUE_REQUIRED,
            description: "Sauvegarde un seul parcours qui est à l'état validé. Préciser l'ID"
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '2048M');

        $io = new SymfonyStyle($input, $output);

        $dpeFullValidDatabase = $input->getOption('dpe-full-valid-database');
        $dpeTodayCfvuValid = $input->getOption('dpe-today-cfvu-valid');

        $singleParcours = $input->getOption('single-parcours');

        $withSkipOption = $input->getOption('with-skip-option');

        if($dpeFullValidDatabase){
            $io->writeln("Sauvegarde de tous les parcours valides en cours...");
            $dpe = $this->entityManager->getRepository(CampagneCollecte::class)->findOneBy(['defaut' => true]);
            $parcoursArray = $this->entityManager->getRepository(Parcours::class)->findAllParcoursForDpe($dpe);
            $parcoursArray = array_filter(
                $parcoursArray, 
                fn($parcours) => $parcours->getFormation()->getTypeDiplome()->getLibelleCourt() !== "MEEF"
            );
            $parcoursArray = array_filter($parcoursArray, 
                fn($p) => $p->getDpeParcours()->last() instanceof DpeParcours
                    && ( 
                        $p->getDpeParcours()->last()->getEtatValidation() === ["publie" => 1]
                        || $p->getDpeParcours()->last()->getEtatValidation() === ["valide_a_publier" => 1]
                    ) 
            );
            $nombreParcours = count($parcoursArray);
            $io->progressStart($nombreParcours);
            try{
                foreach($parcoursArray as $parcours){
                    $this->versioningParcours->saveVersionOfParcours($parcours, new DateTimeImmutable(), isCfvu: true);
                    $io->progressAdvance();
                }
                $now = new DateTimeImmutable('now');
                $dateHeure = $now->format('d-m-Y_H-i-s');
                $logTxt = "[{$dateHeure}] Tous les parcours ont correctement été versionnés en JSON. (Total : {$nombreParcours})\n";
                $io->writeln("\nEnregistrement en base de données...");
                $this->entityManager->flush();
                $this->filesystem->appendToFile(__DIR__ . "/../../versioning_json/success_log/global_save_parcours_success.log", $logTxt);
                $io->success('Sauvegarde en JSON des parcours de la base de données réussie.');
                return Command::SUCCESS;
            }catch(\Exception $e){
                // Affichage de l'erreur sur le terminal
                $io->error("Une erreur est survenue : " . $e->getMessage());
                $now = new DateTimeImmutable('now');
                $dateHeure = $now->format('d-m-Y_H-i-s');
                // Affichage de l'erreur dans les logs// Log error
                $logTxt = "[{$dateHeure}] Le versioning global des parcours a rencontré une erreur.\n{$e->getMessage()}\n";
                $this->filesystem->appendToFile(__DIR__ . "/../../versioning_json/error_log/global_save_parcours_error.log", $logTxt);
            }
        }

        if($dpeTodayCfvuValid){
            $io->writeln("\nSauvegarde des parcours validés en CFVU...");

            $dpe = $this->entityManager->getRepository(CampagneCollecte::class)->findOneBy(['defaut' => true]);
            $parcoursArray = $this->entityManager->getRepository(Parcours::class)->findAllParcoursForDpe($dpe);

            // Exclusion des MEEF
            $parcoursArray = array_filter(
                $parcoursArray, 
                fn($p) => $p->getFormation()->getTypeDiplome()->getLibelleCourt() !== "MEEF"
            );

            // Sélection des parcours validés ("valide_a_publier")
            $parcoursArray = array_filter(
                $parcoursArray,
                function($p) {
                    $dateHistoriquePublication = $this->getHistorique
                        ->getHistoriqueParcoursLastStep(
                            $p->getDpeParcours()->last(), 'valide_a_publier'
                        )?->getDate();

                    $today = new DateTime('now');
                    $dateFormat = "d-m-Y";
    
                    return $today->format($dateFormat) === $dateHistoriquePublication?->format($dateFormat);
                }  
            );

            $nombreParcoursValides = count($parcoursArray);

            try {
                if($nombreParcoursValides > 0){
                    $io->writeln("\nIl y a {$nombreParcoursValides} parcours validés à sauvegarder.");
                    $io->progressStart($nombreParcoursValides);
                        foreach($parcoursArray as $parcoursCfvu){
                            $this->versioningParcours->saveVersionOfParcours(
                                parcours: $parcoursCfvu, 
                                now: new DateTimeImmutable(), 
                                isCfvu: true
                            );
                            $io->progressAdvance(1);
                        }
                        $io->progressFinish();
                    }
                    // Log
                    $now = new DateTimeImmutable();
                    $dateHeure = $now->format('d-m-Y_H-i-s');
                    $io->writeln('Enregistrement en base de données...');
                    // Save into database
                    $this->entityManager->flush();
                    $log = "[{$dateHeure}] Tous les parcours CFVU (total : {$nombreParcoursValides}) ont correctement été versionnés en JSON.\n";
                    $this->filesystem->appendToFile(__DIR__ . "/../../versioning_json/success_log/global_save_parcours_cfvu_success.log", $log);

                    $io->success('Sauvegarde des parcours validés en CFVU reussie !');

                    return Command::SUCCESS;
            }catch(\Exception $e){
                $io->error("Une erreur est survenue : " . $e->getMessage());
                $now = new DateTimeImmutable('now');
                $dateHeure = $now->format('d-m-Y_H-i-s');
                // Affichage de l'erreur dans les logs - Log error
                $logTxt = "[{$dateHeure}] Le versioning global des parcours a rencontré une erreur.\n{$e->getMessage()}\n";
                $this->filesystem->appendToFile(__DIR__ . "/../../versioning_json/error_log/global_save_parcours_error.log", $logTxt);

                return Command::FAILURE;
            }
        }
        if($singleParcours){
            $io->writeln("Sauvegarde d'un parcours dans un fichier JSON");
            if(is_numeric($singleParcours) === false){
                $io->warning("L'identifiant du parcours doit être un nombre. '{$singleParcours}'");
                return Command::INVALID;
            }
            $io->writeln("Récupération du parcours...");
            $parcoursToSave = $this->entityManager->getRepository(Parcours::class)->findOneById($singleParcours);
            if($parcoursToSave){
                $io->writeln("\n[O.K] " . $parcoursToSave->getFormation()->getDisplayLong() . " - " . $parcoursToSave->getDisplay());
                $io->writeln("\n[Sauvegarde CFVU]");
                $continue = $io->ask("Voulez-vous procéder à la sauvegarde ? [Y/n]", 'n', function($answer){
                    return $answer === "Y";
                });

                if($continue === false){
                    $io->writeln("Pas de confirmation. La commande s'arrête.");
                    return Command::SUCCESS;
                }

                // On ne sauvegarde le parcours que s'il est à un état validé ['valide_a_publier', 'publie']
                $haystack = [
                    ['valide_a_publier' => 1],
                    ['publie' => 1]
                ];
                if(in_array($parcoursToSave->getDpeParcours()->last()->getEtatValidation(), $haystack)){
                    $io->writeln("Sauvegarde en cours...");
                    $this->versioningParcours->saveVersionOfParcours(
                        $parcoursToSave,
                        new DateTimeImmutable('now'),
                        isCfvu: true
                    );
                    $this->entityManager->flush();

                    $io->success("Sauvegarde réussie !");
                    return Command::SUCCESS;
                }else {
                    $io->warning("Le parcours n'est pas à un état validé. La sauvegarde ne s'exécutera pas.");
                    return Command::FAILURE;
                }

            }else {
                $io->warning("Aucun parcours ne correspond pour cet identifiant. ({$singleParcours})");
                return Command::INVALID;
            }
        }
        
        $io->warning("Option de la commande non reconnue. Choix possibles : ['single-parcours', 'dpe-full-valid-database', 'dpe-today-cfvu-valid']");
        return Command::INVALID;
    }

}
