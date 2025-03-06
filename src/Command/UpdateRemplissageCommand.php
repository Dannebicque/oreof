<?php

namespace App\Command;

use App\Entity\Parcours;
use App\Repository\FicheMatiereRepository;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-remplissage',
    description: 'Add a short description for your command',
)]
class UpdateRemplissageCommand extends Command
{

    public function __construct(
        private FormationRepository    $formationRepository,
        private ParcoursRepository     $parcoursRepository,
        private FicheMatiereRepository $ficheMatiereRepository,
        private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        //je veux des options ou des arguments pour préciser si c'est fichematiere, formation ou parcours
        $this->addOption(
            'fiche',
            null,
            null,
            'Mettre à jour les fiches matières'
        )
            ->addOption(
                'formation',
                null,
                null,
                'Mettre à jour les formations'
            )
            ->addOption(
                'parcours',
                null,
                null,
                'Mettre à jour les parcours'
            );

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);


        $memory = ini_get('memory_limit');
        $io->writeln('Memory: ' . $memory);
        ini_set('memory_limit', '-1');

        if ($input->getOption('fiche')) {
            $this->updateFiche($io);
        } elseif ($input->getOption('formation')) {
            $this->updateFormation($io);
        } elseif ($input->getOption('parcours')) {
            $this->updateParcours($io);
        }




        ini_set('memory_limit', $memory);


        return Command::SUCCESS;
    }

    private function updateFiche(SymfonyStyle $io): void
    {
        $io->title('Update du remplissage des Fiches');
        $fiches = $this->ficheMatiereRepository->findAll();
        $io->info(count($fiches) . ' fiches à mettre à jour');

        foreach ($fiches as $fiche) {
            $remplissage = $fiche->remplissageBrut();
            $fiche->setRemplissage($remplissage);
            $this->entityManager->flush();
            unset($remplissage);
        }
        $io->success('Remplissages mis à jours pour les fiches');
    }

    private function updateFormation(SymfonyStyle $io)
    {
        $io->title('Update du remplissage des Fiches');
        $fiches = $this->formationRepository->findAll();
        $io->info(count($fiches) . ' formations à mettre à jour');

        foreach ($fiches as $fiche) {
            if ($fiche->getRemplissage()->empty() === true) {
                $fiche->setRemplissage(null);
                $this->entityManager->flush();
            }
        }
        $io->success('Remplissages mis à jours pour les formations');
    }

    private function updateParcours(SymfonyStyle $io)
    {
        $io->title('Update du remplissage des Formations');
        $parcours = $this->parcoursRepository->findAll();
        $io->info(count($parcours) . ' parcours à mettre à jour');
        // mettre une progress bar
        $io->progressStart(count($parcours));


        foreach ($parcours as $parcour) {
            $remplissage = $parcour->remplissageBrut();
            $parcour->setRemplissage($remplissage);
            $io->progressAdvance();
            $this->entityManager->flush();
            unset($remplissage);
        }
        $io->success('Remplissages mis à jours pour les parcours');
    }
}
