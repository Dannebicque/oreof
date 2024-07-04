<?php

namespace App\Command;

use App\Entity\Parcours;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:mccc-pdf',
    description: "Génère les PDF contenant les descriptifs d'une formation ORéOF",
)]
class McccPdfCommand extends Command
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
        $this->addOption(
                'generate-parcours',
                "g:p",
                InputOption::VALUE_REQUIRED,
                "Identifiant (PK) du parcours pour lequel on souhaite générer l'export des MCCC au format PDF"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $generateParcours = $input->getOption("generate-parcours");

        if($generateParcours){
            $parcours = $this->entityManager->getRepository(Parcours::class)->findOneById($generateParcours);
            if($parcours){
                $io->writeln("Récupération du Parcours : [O.K]");
                $io->writeln("\n" . $parcours->getFormation()->getDisplayLong());
                return Command::SUCCESS;
            }
            else {
                $io->warning("L'identifiant du parcours semble incorrect, récupération impossible ({$generateParcours}).");
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
