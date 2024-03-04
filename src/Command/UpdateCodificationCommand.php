<?php

namespace App\Command;

use App\Classes\Codification\CodificationFormation;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-codification',
    description: 'Add a short description for your command',
)]
class UpdateCodificationCommand extends Command
{
    public function __construct(
        protected FormationRepository $formationRepository,
        protected EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $formations = $this->formationRepository->findBy(['dpe' => 1]);
        foreach ($formations as $formation) {
            $codification = new CodificationFormation($this->entityManager);
            $codification->setCodificationFormation($formation);
            $io->success('Formation '. $formation->getDisplay().' codifiée avec succès');
        }

        return Command::SUCCESS;
    }
}
