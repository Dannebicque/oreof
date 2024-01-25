<?php

namespace App\Command;

use App\Classes\CalculStructureParcours;
use App\DTO\StructureEc;
use App\DTO\StructureParcours;
use App\DTO\StructureSemestre;
use App\DTO\StructureUe;
use App\Entity\Parcours;
use App\Repository\ElementConstitutifRepository;
use App\Service\Apogee\Classes\ElementPedagogiDTO6;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:export-elp-apogee',
    description: "Exporte les éléments pédagogiques (ELP) dans l'application APOGEE",
)]
class ExportElpApogeeCommand extends Command
{

    private EntityManagerInterface $entityManager;
    private ElementConstitutifRepository $elementConstitutifRepository;


    public function __construct(
        EntityManagerInterface $entityManager,
        ElementConstitutifRepository $elementConstitutifRepository
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->elementConstitutifRepository = $elementConstitutifRepository;
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'mode',
            mode: InputArgument::OPTIONAL,
            description: 'Execution mode : test or production', 
            default: 'test'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $mode = $input->getOption('mode');

        if($mode === "test"){
            $io->text("Début de la commande d'export des ELP");
            // Récupération de la donnée
            $parcours = $this->entityManager->getRepository(Parcours::class)->findOneById(405);
            $dto = $this->getDTOForParcours($parcours);
            $semestre = $dto->semestres[1]->ues()[0];
            // Transformation en objet pour le SOAP
            $soapObject = $this->setObjectForSoapCall($semestre, $dto);

            dump($soapObject);exit;

            return Command::SUCCESS;
        }

        elseif($mode === "production"){
            $io->warning("Command en mode PRODUCTION - O.K");
            return Command::INVALID;
        }
        else{
            $io->error("Given execution mode is invalid. It should be 'test' or 'production'");
            return Command::FAILURE;
        }
    }

    private function setObjectForSoapCall(
        StructureEc|StructureUe|StructureSemestre $elementPedagogique, 
        StructureParcours $dto
    ) : ElementPedagogiDTO6 {
        return new ElementPedagogiDTO6($elementPedagogique, $dto);
    }

    private function getDTOForParcours(Parcours $parcours){
        $calculStructure = new CalculStructureParcours($this->entityManager, $this->elementConstitutifRepository);
        return $calculStructure->calcul($parcours);
    }
}
