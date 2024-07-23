<?php

namespace App\Command;

use App\Entity\CampagneCollecte;
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

    public function __construct(
        EntityManagerInterface $entityManager,
        Filesystem $filesystem,
        VersioningParcours $versioningParcours
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
        $this->versioningParcours = $versioningParcours;
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'dpe-full-database', 
            mode: InputOption::VALUE_NONE,
            description: 'Sauvegarde tous les parcours de la base de données en JSON'
        )
        ->addOption(
            name: 'dpe-only-cfvu-valid',
            mode: InputOption::VALUE_NONE,
            description: 'Sauvegarde tous les parcours qui sont validés CFVU (dernier état à "valide_a_publier").',
        )
        ->addOption(
            name: 'with-skip-option',
            mode: InputOption::VALUE_NONE,
            description: "Ne sauvegarde pas une version de parcours, si la dernière version est validée CFVU"
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '2048M');

        $io = new SymfonyStyle($input, $output);

        $dpeFullDatabase = $input->getOption('dpe-full-database');
        $dpeOnlyCfvuValid = $input->getOption('dpe-only-cfvu-valid');

        $withSkipOption = $input->getOption('with-skip-option');

        if($dpeFullDatabase){
            $io->writeln("Sauvegarde de tous les parcours en cours...");
            $dpe = $this->entityManager->getRepository(CampagneCollecte::class)->findOneBy(['defaut' => true]);
            $parcoursArray = $this->entityManager->getRepository(Parcours::class)->findAllParcoursForDpe($dpe);
            $parcoursArray = array_filter(
                $parcoursArray, 
                fn($parcours) => $parcours->getFormation()->getTypeDiplome()->getLibelleCourt() !== "MEEF"
            );
            $io->progressStart(count($parcoursArray));
            try{
                foreach($parcoursArray as $parcours){
                    $this->versioningParcours->saveVersionOfParcours($parcours, new DateTimeImmutable());
                    $io->progressAdvance();
                }
                $now = new DateTimeImmutable('now');
                $dateHeure = $now->format('d-m-Y_H-i-s');
                $logTxt = "[{$dateHeure}] Tous les parcours ont correctement été versionnés en JSON.\n";
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

        if($dpeOnlyCfvuValid){
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
                fn($p) => $p->getDpeParcours()->last()->getEtatValidation() === ['valide_a_publier' => 1]
                          && 
                            ((new DateTime())->getTimestamp() 
                            - $p->getDpeParcours()->last()->getCreated()->getTimestamp()
                            ) <= 86400                           
                            // Différence entre les deux dates inférieure à un jour (86400 secondes)
            );

            $nombreParcoursValides = count($parcoursArray);

            try {
                if($nombreParcoursValides > 0){
                    $io->writeln("\nIl y a {$nombreParcoursValides} validés à sauvegarder.");
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
        
        $io->warning("Option de la commande non reconnue. Choix possibles : ['dpe-full-database', 'dpe-only-cfvu-valid']");
        return Command::INVALID;
    }

}
