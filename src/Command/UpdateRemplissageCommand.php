<?php

namespace App\Command;

use App\Entity\CampagneCollecte;
use App\Repository\CampagneCollecteRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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
        private readonly CampagneCollecteRepository $collecteRepository,
        private readonly FormationRepository        $formationRepository,
        private readonly ParcoursRepository         $parcoursRepository,
        private readonly FicheMatiereRepository     $ficheMatiereRepository,
        private readonly EntityManagerInterface     $entityManager
    ) {
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

        //je veux un argument pour préciser la campagne

        $this->addOption('campagne', null, InputOption::VALUE_REQUIRED, 'Campagne à traiter');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $idCampagne = $input->getOption('campagne');
        $campagne = $this->collecteRepository->find($idCampagne);

        if (null === $campagne) {
            $io->error('Campagne non trouvée');
            return Command::FAILURE;
        }


        $memory = ini_get('memory_limit');
        $io->writeln('Memory: ' . $memory);
        ini_set('memory_limit', '-1');

        if ($input->getOption('fiche')) {
            $this->updateFiche($io, $campagne);
        } elseif ($input->getOption('formation')) {
            $this->updateFormation($io);
        } elseif ($input->getOption('parcours')) {
            $this->updateParcours($io);
        }




        ini_set('memory_limit', $memory);


        return Command::SUCCESS;
    }

    private function updateFiche(SymfonyStyle $io, CampagneCollecte $campagneCollecte): void
    {
        $io->title('Update du remplissage des Fiches');
        $fiches = $this->ficheMatiereRepository->findBy(['campagneCollecte' => $campagneCollecte]);
        $io->info(count($fiches) . ' fiches à mettre à jour');
        $totalFiches = count($fiches);
        $io->progressStart($totalFiches);

        $batchSize = 20;
        $i = 0;

        foreach ($fiches as $fiche) {
            if ($fiche->getEtatFiche() === []) {
                $remplissage = $fiche->remplissageBrut();
                $fiche->setRemplissage($remplissage);
                if ($remplissage->isFull()) {
                    $fiche->setEtatFiche(['soumis_central' => 1]);
                } else {
                    $fiche->setEtatFiche(['en_cours_redaction' => 1]);
                }
                $this->entityManager->persist($fiche);
                $i++;
                if (($i % $batchSize) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
                $io->progressAdvance();
                unset($remplissage);
            } else {
                $io->progressAdvance();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        $io->success('Remplissages mis à jours pour les fiches');
    }

    private function updateFormation(SymfonyStyle $io): void
    {
        $io->title('Update du remplissage des formations');
        $formations = $this->formationRepository->findAll();
        $io->info(count($formations) . ' formations à mettre à jour');
        $io->progressStart(count($formations));
        foreach ($formations as $formation) {
            $formation->setRemplissage(null);
            $io->progressAdvance();
            $this->entityManager->flush();
            unset($remplissage);
        }
        $io->success('Remplissages mis à jours pour les formations');
    }

    private function updateParcours(SymfonyStyle $io): void
    {
        $io->title('Update du remplissage des Formations');
        $parcours = $this->parcoursRepository->findAll();
        $io->info(count($parcours) . ' parcours à mettre à jour');
        // mettre une progress bar
        $io->progressStart(count($parcours));


        foreach ($parcours as $parcour) {
            $parcour->setRemplissage(null);
            $io->progressAdvance();
            $this->entityManager->flush();
            unset($remplissage);
        }
        $io->success('Remplissages mis à jours pour les parcours');
    }
}
