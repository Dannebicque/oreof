<?php

namespace App\Command;

use App\Entity\DpeParcours;
use App\Enums\TypeModificationDpeEnum;
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
    name: 'app:update-dpe',
    description: 'Add a short description for your command',
)]
class UpdateDpeCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected FormationRepository $formationRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }


    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $date = new \DateTime('2023-09-01');

        $formations = $this->formationRepository->findAll();

        foreach ($formations as $formation) {
            foreach ($formation->getParcours() as $parcours) {
                if ($parcours->getDpeParcours()->count() === 0) {
                    $dpe = new DpeParcours();
                    $dpe->setParcours($parcours);
                    $dpe->setDpe($formation->getDpe());
                    $dpe->setFormation($formation);
                    $dpe->setCreated($date);
                    $dpe->setVersion('1.0');
                    $dpe->setEtatReconduction(TypeModificationDpeEnum::CREATION);
                    $dpe->setEtatValidation($formation->getEtatDpe());
                    $this->entityManager->persist($dpe);
                }
            }
            $io->success('Formation : '.$formation->getId());
            $this->entityManager->flush();
        }


        $io->success('Recopie effectuée.');

        return Command::SUCCESS;
    }
}
