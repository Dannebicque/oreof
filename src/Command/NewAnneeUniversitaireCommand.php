<?php

namespace App\Command;

use App\Entity\CampagneCollecte;
use App\Entity\DpeParcours;
use App\Entity\Formation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:new-annee-universitaire',
    description: 'Duplique tous les parcours et formations, pour créer une nouvelle année universitaire',
)]
class NewAnneeUniversitaireCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private string $slugSuffix = "-2025";

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'generate-full-database', 
            mode: InputOption::VALUE_NONE, 
            description: 'Copie tous les parcours disponibles'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '2500M');

        $io = new SymfonyStyle($input, $output);
        
        $generateFullDatabase = $input->getOption('generate-full-database');

        if($generateFullDatabase){
            $io->writeln("\nCommande pour copier les parcours et formations sur une nouvelle année universitaire.\n");
            $io->writeln("La commande va copier l'année actuelle pour créer l'année 2025-2026\n");
            if($io->ask("Souhaitez-vous poursuivre ? [Y/n]", 'n') === "Y"){
                $newCampagneCollecte = $this->entityManager
                    ->getRepository(CampagneCollecte::class)
                    ->findOneById(2);
                $io->writeln("Récupération des formations...");
                $formationArray = $this->entityManager->getRepository(Formation::class)->findAll();
                $nbFormation = count($formationArray);
                $io->writeln("Il y a {$nbFormation} formations à dupliquer.");
                $io->writeln("Copie en cours...");
                foreach($formationArray as $formation){
                    // Clone de la formation
                    $formationClone = clone $formation;
                    $formationClone->setSlug($formation->getSlug() . $this->slugSuffix);
                    $formationClone->setFormationOrigineCopie($formation);
                    foreach($formationClone->getParcours() as $parcours){
                        // Clone du Parcours
                        $parcoursClone = clone $parcours;
                        $parcoursClone->setFormation($formationClone);
                        $parcoursClone->setParcoursOrigineCopie($parcours);
                        // Clone du DpeParcours
                        $dpeParcours = $this->entityManager
                            ->getRepository(DpeParcours::class)
                            ->findOneBy(['parcours' => $parcours, 'formation' => $formation]);
                        if($dpeParcours){
                            $dpeParcoursClone = clone $dpeParcours;
                            $dpeParcoursClone->setParcours($parcoursClone);
                            $dpeParcoursClone->setFormation($formationClone);
                            $dpeParcoursClone->setCampagneCollecte($newCampagneCollecte);
                            $this->entityManager->persist($dpeParcoursClone);
                        }
                        // Sauvegarde en BD
                        $this->entityManager->persist($parcoursClone);
                    }
                    $this->entityManager->persist($formationClone);
                }
                $io->writeln("Application des changements...");
                $this->entityManager->flush();
                $io->success("Copie réussie !");
                return Command::SUCCESS;
            }
            
            $io->writeln("Commande annulée");
            return Command::SUCCESS;
        }

        $io->warning("Option non reconnue. Doit être parmi ['generate-full-database']");
        return Command::SUCCESS;
    }

}
