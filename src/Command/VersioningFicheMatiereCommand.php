<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:versioning-fiche-matiere',
    description: "Sauvegarde d'une ou plusieurs fiches matières en JSON",
)]
class VersioningFicheMatiereCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'dpe-full-database', 
            mode: InputOption::VALUE_NONE,
            description: 'Sauvegarde les fiches matières pour tous les parcours de la DPE actuelle');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dpeFullDatabase = $input->getOption('dpe-full-database');

        if ($dpeFullDatabase) {
            $io->writeln("Sauvegarde de toutes les fiches matières en cours...");

            $io->success("Sauvegarde réussie !");
            return Command::SUCCESS;
        }

        $io->warning("Option de la commande non reconnue. Choix possibles : ['dpe-full-database']");
        return Command::INVALID;
    }
}
