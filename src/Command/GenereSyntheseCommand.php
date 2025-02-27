<?php

namespace App\Command;

use App\Classes\GenereSynthese;
use App\Classes\MyGotenbergPdf;
use App\Repository\CampagneCollecteRepository;
use App\Repository\ComposanteRepository;
use App\Repository\ParcoursRepository;
use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:genere-synthese',
    description: 'Add a short description for your command',
)]
class GenereSyntheseCommand extends Command
{
    private string $publicDir;

    public function __construct(
        protected CampagneCollecteRepository $campagneCollecteRepository,
        protected GenereSynthese $genereSynthese,
        protected ParcoursRepository $parcoursRepository,
        protected ComposanteRepository $composanteRepository,
        protected MyGotenbergPdf $myGotenbergPdf,
        KernelInterface $kernel
    ) {
        parent::__construct();
        $this->publicDir = $kernel->getProjectDir().'/public/';
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dpe = $this->campagneCollecteRepository->find(1);
        gc_enable();
        $composantes = $this->composanteRepository->findAll();
        foreach ($composantes as $composante) {
            //récupérer uniquement les DPE ouverts
            $parcours = $this->parcoursRepository->findByTypeValidationAttenteCfvuAndComposante($dpe, 'soumis_central', $composante); //soumis_cfvu

            foreach ($parcours as $parc) {
                $tDemandes = $this->genereSynthese->getSyntheseByParcours($parc, $composante, $dpe);
                if (count($tDemandes) > 0) {
                    if (!is_dir($this->publicDir.'uploads/syntheses/'.$composante->getSigle())) {
                        if (!mkdir($concurrentDirectory = $this->publicDir . 'uploads/syntheses/' . $composante->getSigle()) && !is_dir($concurrentDirectory)) {
                            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                        }
                    }

                    $this->myGotenbergPdf->renderAndSave(
                        'pdf/synthese_modifications.html.twig',
                        'uploads/syntheses/'.$composante->getSigle().'/',
                        [
                            'titre' => 'Liste des demande de changement MCCC et maquettes',
                            'demande' => $tDemandes,
                            'composante' => $composante,
                            'dpe' => $dpe,
                        ],
                        'synthese_cfvu_'.$parc->getSigle().'-'. (new DateTime())->format('d-m-Y_H-i-s')
                    );
                }
                unset($tDemandes['parcours'], $tDemandes['diffStructure'], $tDemandes['dto']);
                unset($tDemandes);
                gc_collect_cycles();
            }
        }
        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
