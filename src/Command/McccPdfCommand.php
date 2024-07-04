<?php

namespace App\Command;

use App\Entity\CampagneCollecte;
use App\Entity\Parcours;
use App\TypeDiplome\Export\ButMccc;
use App\TypeDiplome\Export\LicenceMccc;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:mccc-pdf',
    description: "Génère les PDF contenant les descriptifs d'une formation ORéOF",
)]
class McccPdfCommand extends Command
{

    private EntityManagerInterface $entityManager;

    private HttpClientInterface $httpClient;

    private Filesystem $fs;

    // Types des formations
    private LicenceMccc $licenceMccc;
    private ButMccc $butMccc;

    public function __construct(
        EntityManagerInterface $entityManager,
        Filesystem $fs,
        HttpClientInterface $httpClient,
        LicenceMccc $licenceMccc
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->fs = $fs;
        $this->httpClient = $httpClient;

        $this->licenceMccc = $licenceMccc;
    }

    protected function configure(): void
    {
        $this->addOption(
                name: 'generate-parcours',
                mode: InputOption::VALUE_REQUIRED,
                description: "Identifiant (PK) du parcours pour lequel on souhaite générer l'export des MCCC au format PDF"
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

                $anneeDpe = $this->entityManager->getRepository(CampagneCollecte::class)->findOneBy(['defaut' => 1]);
                $pdf = $this->licenceMccc->exportPdfLicenceMccc(
                    anneeUniversitaire: $anneeDpe,
                    parcours : $parcours,
                );
                
                $fileName = "MCCC - " . $anneeDpe->getAnnee() . " - " . $parcours->getFormation()->getSlug() ?? '---';

                $this->fs->appendToFile(
                    __DIR__ . "/../../mccc-export/" . $fileName . ".pdf",
                    $pdf
                );

                $io->success("Fichier généré avec succès.");

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
