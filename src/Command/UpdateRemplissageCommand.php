<?php

namespace App\Command;

use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-remplissage',
    description: 'Add a short description for your command',
)]
class UpdateRemplissageCommand extends Command
{

    public function __construct(
        private FormationRepository $formationRepository,
        private ParcoursRepository $parcoursRepository,
        private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $parcours = $this->parcoursRepository->findAll();
        $formations = $this->formationRepository->findAll();

        $memory = ini_get('memory_limit');
        $io->writeln('Memory: ' . $memory);
        ini_set('memory_limit', '-1');

        foreach ($parcours as $parc) {
            if ($parc->getRemplissage()->empty() === true) {
                //$parc->setRemplissage(null);
                $this->entityManager->flush();
            }
        }

        foreach ($formations as $formation) {
            if ($formation->getRemplissage()->empty() === true) {
                //$formation->setRemplissage(null);
                $this->entityManager->flush();
            }
        }
        ini_set('memory_limit', $memory);
        $io->success('Remplissages mis à jours.');

        return Command::SUCCESS;
    }
}
