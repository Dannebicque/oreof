<?php

namespace App\Command;

use App\Entity\CampagneCollecte;
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $anneeSource = $input->getOption('annee-source');

        if(isset($anneeSource) === false){
            $io->error("Il faut définir l'année à copier (PK : Campagne Collecte) !");
            return Command::INVALID;
        }

        $campagneCollecteSource = $this->entityManager
            ->getRepository(CampagneCollecte::class)
            ->findOneById($anneeSource);

        if(!$campagneCollecteSource){
            $io->error("Aucune campagne de collecte n'a été trouvée pour cet identifiant ({$anneeSource}).");
            return Command::INVALID;
        }

        $io->success("La commande s'est exécutée correctement !");

        return Command::SUCCESS;
    }
}