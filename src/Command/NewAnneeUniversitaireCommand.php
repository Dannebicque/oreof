<?php

namespace App\Command;

use App\Entity\CampagneCollecte;
use App\Entity\DpeParcours;
use App\Entity\FicheMatiere;
use App\Entity\FicheMatiereMutualisable;
use App\Entity\Formation;
use App\Entity\Parcours;
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

    private int $idCampagneCollecte = 2;

    private int $stepFormationFlush =  35;

    private int $stepFicheMatiereFlush = 1000;

    private int $stepMutualisations = 1000;

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
        ini_set('memory_limit', '5000M');

        $io = new SymfonyStyle($input, $output);
        
        $generateFullDatabase = $input->getOption('generate-full-database');

        if($generateFullDatabase){
            $parcoursRepository = $this->entityManager->getRepository(Parcours::class);
            $ficheMatiereRepository = $this->entityManager->getRepository(FicheMatiere::class);

            $io->writeln("\nCommande pour copier les parcours et formations sur une nouvelle année universitaire.\n");
            $io->writeln("La commande va copier l'année actuelle pour créer l'année 2025-2026\n");
            if($io->ask("Souhaitez-vous poursuivre ? [Y/n]", 'n') === "Y"){
                /**
                 * 
                 * FORMATION
                 * 
                 */
                $formationArray = $this->entityManager->getRepository(Formation::class)->findBy([], ['id' => 'ASC']);
                $nbFormation = count($formationArray);
                $io->writeln("Copie des formations...");
                $io->progressStart($nbFormation);
                foreach($formationArray as $formation){
                    $formationClone = clone $formation;
                    $formationClone->setSlug($formation->getSlug() . $this->slugSuffix);
                    $formationClone->setFormationOrigineCopie($formation);
                    $this->entityManager->persist($formationClone);
                    $io->progressAdvance(1);
                }
                $io->progressFinish();
                $io->writeln("Application des changements...");
                $this->entityManager->flush();
                $formationArray = null;
                /**
                 * 
                 * PARCOURS
                 * 
                 */
                $io->writeln("Copie des parcours...");
                $parcoursArray = $parcoursRepository->findBy([], ['id' => 'ASC']);
                $nbParcours = count($parcoursArray);
                $io->progressStart($nbParcours);
                foreach($parcoursArray as $parcours){
                    $parcoursClone = clone $parcours;
                    $newCloneFormation = $this->entityManager->getRepository(Formation::class)
                        ->findOneBy(['formationOrigineCopie' => $parcours->getFormation()]);
                    $parcoursClone->setFormation($newCloneFormation);
                    $parcoursClone->setParcoursOrigineCopie($parcours);
                    $this->entityManager->persist($parcoursClone);
                    $io->progressAdvance(1);
                }
                $io->progressFinish();
                $io->writeln("Application des changements...");
                $this->entityManager->flush();
                $parcoursArray = null;
                /**
                 * 
                 * DPE PARCOURS
                 * 
                 */
                $io->writeln("Copie du DPE Parcours...");
                $dpeArray = $this->entityManager->getRepository(DpeParcours::class)->findBy([], ['id' => 'ASC']);
                $nbDpe = count($dpeArray);
                $io->progressStart($nbDpe);
                $newCampagneCollecte = $this->entityManager
                    ->getRepository(CampagneCollecte::class)
                    ->findOneById($this->idCampagneCollecte);
                foreach($dpeArray as $dpe){
                    $dpeParcoursClone = clone $dpe;
                    $newFormationDpe = $this->entityManager->getRepository(Formation::class)
                        ->findOneBy(['formationOrigineCopie' => $dpe->getFormation()]);
                    $newParcoursDpe = $parcoursRepository
                        ->findOneBy(['parcoursOrigineCopie' => $dpe->getParcours()]);
                    $dpeParcoursClone->setParcours($newParcoursDpe);
                    $dpeParcoursClone->setFormation($newFormationDpe);
                    $dpeParcoursClone->setCampagneCollecte($newCampagneCollecte);
                    $this->entityManager->persist($dpeParcoursClone);
                    $io->progressAdvance(1);                    
                }
                $io->progressFinish();
                $io->writeln("Application des changements...");
                $this->entityManager->flush();
                $dpeArray = null;
                /**
                 * 
                 * FICHES MATIERES
                 * 
                 */
                $nbFicheMatiere = count($this->entityManager->getRepository(FicheMatiere::class)->findBy([]));
                $processedFicheMatiere = 0;
                $io->writeln("Copie des fiches matières...");
                $io->progressStart($nbFicheMatiere);
                // Pagination de la table des fiches matières
                for($i = 0; $processedFicheMatiere < $nbFicheMatiere; $i++){
                    $ficheMatiereArray = array_slice(
                        $this->entityManager->getRepository(FicheMatiere::class)
                            ->findBy([], ['id' => 'ASC']),
                        ($i * $this->stepFicheMatiereFlush),
                        $this->stepFicheMatiereFlush    
                    );
                    foreach($ficheMatiereArray as $ficheMatiere){
                        $ficheMatiereClone = clone $ficheMatiere;
                        // Le slug est unique, donc on le préfixe
                        $ficheMatiereClone->setSlug($ficheMatiereClone->getSlug() . $this->slugSuffix);
                        // Le parcours devient le nouveau
                        if($ficheMatiere->getParcours() !== null){
                            $ficheMatiereClone->setParcours(
                                $parcoursRepository->findOneBy(['parcoursOrigineCopie' => $ficheMatiere->getParcours()])
                            );
                        }
                        $ficheMatiereClone->setFicheMatiereOrigineCopie($ficheMatiere);
                        // Sauvegarde en BD
                        $this->entityManager->persist($ficheMatiereClone);
                        ++$processedFicheMatiere;
                        $io->progressAdvance(1);
                    }
                }
                // Libération de la mémoire
                $ficheMatiereArray = null;
                $io->progressFinish();
                $io->writeln("Application des changements...");
                $this->entityManager->flush();
                /**
                 * 
                 * FICHES MATIERES MUTUALISABLES
                 * 
                 */
                $nbMutualisations = count($this->entityManager->getRepository(FicheMatiereMutualisable::class)->findBy([]));
                $processedMutualisations = 0;
                $io->writeln("Copie des mutualisations des fiches matières...");
                $io->progressStart($nbMutualisations);
                for($i = 0; $processedMutualisations < $nbMutualisations; $i++){
                    $mutualisationArray = array_slice(
                        $this->entityManager->getRepository(FicheMatiereMutualisable::class)
                            ->findBy([], ['id' => 'ASC']),
                        ($i * $this->stepMutualisations),
                        $this->stepMutualisations
                    );
                    foreach($mutualisationArray as $mutualisationFM){
                        $mutualisationFMClone = clone $mutualisationFM;
                        if($mutualisationFM->getParcours() !== null){
                            $mutualisationFMClone->setParcours(
                                $parcoursRepository->findOneBy(['parcoursOrigineCopie' => $mutualisationFM->getParcours()])
                            );
                        }
                        if($mutualisationFM->getFicheMatiere() !== null){
                            $mutualisationFMClone->setFicheMatiere(
                                $ficheMatiereRepository->findOneBy(['ficheMatiereOrigineCopie' => $mutualisationFM->getFicheMatiere()])
                            );
                        }
                        $this->entityManager->persist($mutualisationFMClone);
                        ++$processedMutualisations;
                        $io->progressAdvance(1);
                    }
                }
                $io->progressFinish();
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
