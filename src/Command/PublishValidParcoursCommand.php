<?php

namespace App\Command;

use App\Classes\GetHistorique;
use App\Entity\CampagneCollecte;
use App\Entity\DpeParcours;
use App\Entity\HistoriqueParcours;
use App\Entity\Parcours;
use App\Entity\User;
use App\Enums\TypeModificationDpeEnum;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Workflow\WorkflowInterface;

#[AsCommand(
    name: 'app:publish-valid-parcours',
    description: 'Add a short description for your command',
)]
class PublishValidParcoursCommand extends Command
{

    private EntityManagerInterface $entityManager;

    private WorkflowInterface $dpeParcoursWorkflow;

    private GetHistorique $getHistorique;

    private Filesystem $filesystem;

    public function __construct(
        EntityManagerInterface $entityManager,
        WorkflowInterface $dpeParcoursWorkflow,
        GetHistorique $getHistorique,
        Filesystem $filesystem
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->dpeParcoursWorkflow = $dpeParcoursWorkflow;
        $this->getHistorique = $getHistorique;
        $this->filesystem = $filesystem;
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'date-is-today', 
            mode: InputOption::VALUE_NONE, 
            description: "Met à jour le workflow et l'historique pour les parcours à publier aujourd'hui"
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '2048M');

        $io = new SymfonyStyle($input, $output);
        
        $dateIsToday = $input->getOption('date-is-today');

        if ($dateIsToday) {
            $io->writeln("Récupération des parcours à publier aujourd'hui...");

            $today = new DateTime('now');

            $dpe = $this->entityManager->getRepository(CampagneCollecte::class)->findOneBy(['defaut' => true]);

            $parcoursArray = $this->entityManager->getRepository(Parcours::class)
                ->findAllParcoursForDpe($dpe);
                
            // Récupération des parcours à publier aujourd'hui
            $parcoursArray = array_filter(
                $parcoursArray,
                function($p) use ($today) {
                    $dateHistoriqueValide = $this->getHistorique
                        ->getHistoriqueParcoursLastStep(
                            $p->getDpeParcours()->last(), 'valide_a_publier'
                        )?->getDate();

                    $dateFormat = "d-m-Y";

                    return $dateHistoriqueValide?->format($dateFormat) === $today->format($dateFormat);
                }  
            );
            
            $nombreParcours = count($parcoursArray);
            $io->writeln("Il y a {$nombreParcours} parcours à traiter.");

            try {
                if($nombreParcours > 0){                
                    // Récupérer l'utilisateur admin
                    $adminUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'admin']);

                    // Pour chacun d'entre eux, créer un historique 'publication' et faire avancer le workflow
                    foreach($parcoursArray as $p){
                        $now = new DateTime();
                        $historiqueParcours = new HistoriqueParcours();
                        $historiqueParcours->setParcours($p);
                        $historiqueParcours->setDate($now);
                        $historiqueParcours->setUser($adminUser);
                        $historiqueParcours->setEtape('publication');
                        $historiqueParcours->setEtat('valide');

                        $dpeParcours = $p->getDpeParcours()->last();
                        $this->dpeParcoursWorkflow->apply($dpeParcours, 'publier');

                        $this->entityManager->persist($historiqueParcours);
                    }

                    $this->entityManager->flush();
                }

                $now = (new DateTime())->format('d-m-Y_H-i-s');
                $logTxt = "[{$now}] La publication des parcours s'est bien déroulée. (Total : {$nombreParcours})\n";
                $this->filesystem->appendToFile(__DIR__ . "/../../publication-cron/publication-cron.success.log", $logTxt);

                return Command::SUCCESS;

            }catch(\Exception $e){
                $now = (new DateTime())->format('d-m-Y_H-i-s');
                $logTxt = "[{$now}] La publication des parcours a rencontré une erreur.\nMessage : {$e->getMessage()}\n";
                $this->filesystem->appendToFile(__DIR__ . "/../../publication-cron/publication-cron.error.log", $logTxt);
                $io->writeln("Le script a rencontré une erreur. Le fichier de log a été généré.");

                return Command::FAILURE;
            }       
        }
    }
}