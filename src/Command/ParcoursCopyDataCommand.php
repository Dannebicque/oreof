<?php

namespace App\Command;

use App\Entity\Parcours;
use App\Service\ParcoursCopyData;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:parcours-copy-data',
    description: 'Commande pour la migration des heures vers les fiches matières',
)]
class ParcoursCopyDataCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private ParcoursCopyData $parcoursCopyData;

    private Filesystem $fs;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParcoursCopyData $parcoursCopyData,
        Filesystem $fs
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->parcoursCopyData = $parcoursCopyData;
        $this->fs = $fs;
    }

    protected function configure(): void
    {
        $this
        ->addOption(
            name: 'dto-pdf-export', 
            mode: InputOption::VALUE_REQUIRED, 
            description: 'Commande pour exporter un DTO de parcours au format PDF'
        )->addOption(
            name: 'after-copy',
            mode: InputOption::VALUE_NONE,
            description: 'Si le résultat doit être généré après copie sur les fiches matières'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dtoPdfExport = $input->getOption('dto-pdf-export');
        $afterCopy = $input->getOption('after-copy');

        if($dtoPdfExport){
            try{
                $io->writeln("Récupération du parcours en base de données...\n");
                $parcours = $this->entityManager->getRepository(Parcours::class)->findOneById($dtoPdfExport);
                if($parcours){
                    $io->writeln("[O.K] - Parcours trouvé : {$parcours->getDisplay()}\n");
                    $io->writeln("Génération de l'export PDF en cours...");

                    $now = (new DateTime())->format('d-m-Y_H-i-s');
                    $path = __DIR__ . "/../../export/";
                    $fileName = "{$now}-Maquette-DTO-Parcours-{$dtoPdfExport}";
                    if($afterCopy){
                        $fileName .= "-after-copy.pdf";
                        $pdf = $this->parcoursCopyData->exportDTOAsPdf($parcours, true, true);
                    }else {
                        $fileName .= "-initial.pdf";
                        $pdf = $this->parcoursCopyData->exportDTOAsPdf($parcours, false);
                    }

                    $this->fs->appendToFile($path . $fileName, $pdf);
                    $io->writeln("Fichier généré avec succès. (Dossier '/export')");

                    return Command::SUCCESS;
                }
                else {
                    $io->warning("Aucun parcours trouvé pour cet identifiant. ({$dtoPdfExport})");
                    return Command::FAILURE;
                }
                }
                catch(\Exception $e){
                    $io->writeln("Une erreur est survenue.");
                    $io->writeln("Message d'erreur : {$e->getMessage()}");
        
                    return Command::FAILURE;
            }
        }

        $io->warning("Option non reconnue. Doit être parmi ['dto-pdf-export']\n");

        return Command::INVALID;
    }
}
