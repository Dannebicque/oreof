<?php

namespace App\Command;

use App\Repository\BlocCompetenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-code-bcc',
    description: 'Add a short description for your command',
)]
class UpdateCodeBccCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected BlocCompetenceRepository $blocCompetenceRepository,
        string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $blocs = $this->blocCompetenceRepository->findAll();

        foreach ($blocs as $bloc) {
            $bloc->genereCode();
            foreach ($bloc->getCompetences() as $competence) {
                $competence->genereCode();
            }
        }

        $this->entityManager->flush();

        $io->success('Codification des BCC mise à jour');

        return Command::SUCCESS;
    }
}
