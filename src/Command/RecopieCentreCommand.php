<?php

namespace App\Command;

use App\Entity\UserCentre;
use App\Repository\UserCentreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:recopie-centre',
    description: 'Add a short description for your command',
)]
class RecopieCentreCommand extends Command
{
    public function __construct(
        private UserCentreRepository   $userCentreRepository,
        private EntityManagerInterface $entityManager,

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

        // récupérer tous les centres qui sont sur parcours ou formation
        // recréer une entrée avec la formation ou le parcours recopié sur la campagne suivante
        $centres = $this->userCentreRepository->findAll();
        foreach ($centres as $centre) {
            if ($centre->getFormation() !== null) {
                $newCentre = new UserCentre();
                $newCentre->setUser($centre->getUser());
                $newCentre->setFormation($centre->getFormation()->getFormationOrigineCopie());
                $newCentre->setDroits($centre->getDroits());
                $this->entityManager->persist($newCentre);
                unset($newCentre);
            }
        }

        $this->entityManager->flush();



        $io->success('Centres mis à jour.');

        return Command::SUCCESS;
    }
}
