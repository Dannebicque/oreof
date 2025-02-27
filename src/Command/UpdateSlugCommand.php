<?php

namespace App\Command;

use App\Repository\FicheMatiereRepository;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-slug',
    description: 'Add a short description for your command',
)]
class UpdateSlugCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FicheMatiereRepository $ficheRepository,
        private FormationRepository $formationRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1 === 'fiche') {
            $io->note(sprintf('You passed an argument: %s', $arg1));
            $fiches = $this->ficheRepository->findAll();
            foreach ($fiches as $fiche) {
                $fiche->setSlug(null);
                $this->entityManager->persist($fiche);
            }
            $this->entityManager->flush();
        } else if ($arg1 === 'formation') {
            $io->note(sprintf('You passed an argument: %s', $arg1));
            $fiches = $this->formationRepository->findAll();
            foreach ($fiches as $fiche) {
                $fiche->updateSlug();
                $this->entityManager->persist($fiche);
            }
            $this->entityManager->flush();
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
