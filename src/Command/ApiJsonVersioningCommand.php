<?php

namespace App\Command;

use App\Service\ApiJsonExport;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:api-json-versioning',
    description: "Commande pour gérer l'API JSON du versioning",
)]
class ApiJsonVersioningCommand extends Command
{
    private Filesystem $fs;

    private ApiJsonExport $apiJsonExport;

    public function __construct(
        Filesystem $fs,
        ApiJsonExport $apiJsonExport
    )
    {
        parent::__construct();
        $this->fs = $fs;
        $this->apiJsonExport = $apiJsonExport;
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                name: 'generate-index-api', 
                mode: InputOption::VALUE_NONE, 
                description: "Génère le fichier d'index pour l'API JSON du versioning"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $generateIndexApi = $input->getOption('generate-index-api');

        if($generateIndexApi){
            ini_set('memory_limit', '2500M');
            ini_set('max_execution_time', '300');

            $filename = "api_json_urca_versioning.json";
            $path = __DIR__ . "/../../public/api_json/";

            $io->writeln("Génération de l'index de l'API JSON en cours...");

            if($this->fs->exists($path . $filename)){
                $now = (new \DateTime())->format('d-m-Y_H-i');
                $this->fs->rename($path . $filename, $path . $now . "-" .  $filename);
            }
            $apiJson = $this->apiJsonExport->generateApiVersioning($io);
            $this->fs->appendToFile($path . $filename, json_encode($apiJson));

            $io->success("Index de l'API JSON (versioning) créé avec succès !");
            return Command::SUCCESS;
        }


        return Command::SUCCESS;
    }
}
