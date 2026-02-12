<?php

namespace App\Command;

use App\Classes\Codification\CodificationFormation;
use App\Repository\CampagneCollecteRepository;
use App\Repository\FormationRepository;
use App\Repository\TypeDiplomeRepository;
use App\TypeDiplome\TypeDiplomeResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-codification',
    description: 'Génére la codification des formations',
)]
class UpdateCodificationCommand extends Command
{
    public function __construct(
        protected TypeDiplomeResolver $typeDiplomeResolver,
        protected CampagneCollecteRepository $campagneCollecteRepository,
        protected FormationRepository $formationRepository,
        protected TypeDiplomeRepository $typeDiplomeRepository,
        protected EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('typeDiplome', InputArgument::REQUIRED, 'Le type de diplôme (L, LP, BUT, M, BUT, ...')
        ;

        // ajoute une option sur ma commande pour que l'utilisateur puisse spécifier le niveau de codification entre les valeurs haute, basse et complet
        $this
            ->addArgument('niveauCodification', InputArgument::REQUIRED, 'Le niveau de codification (haute, basse, complet)');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $campagne = $this->campagneCollecteRepository->find(1);

        if (!$campagne) {
            $io->error('Aucune campagne de collecte n\'est active');
            return Command::FAILURE;
        }

        // vérifier si le type de diplôme est valide
        $typeDiplome = $input->getArgument('typeDiplome');
        $td = $this->typeDiplomeRepository->findOneBy(['libelle_court' => $typeDiplome]);
        if (!$td) {
            $io->error('Le type de diplôme '. $typeDiplome .' n\'existe pas');
            return Command::FAILURE;
        }

        // vérifier si le niveau de codification est valide
        $niveauCodification = $input->getArgument('niveauCodification');
        if (!in_array($niveauCodification, ['haute', 'basse', 'complet'])) {
            $io->error('Le niveau de codification '. $niveauCodification .' n\'est pas valide');
            return Command::FAILURE;
        }


        $formations = $this->formationRepository->findByDpeAndTypeDiplome($campagne, $td);
        foreach ($formations as $formation) {
            $typeD = $this->typeDiplomeResolver->fromFormation($formation);
            $codification = new CodificationFormation($this->entityManager, $typeD);
            //utiliser la bonne méthode selon le niveau de codification

            if ($niveauCodification === 'haute') {
                $codification->setCodificationHaute($formation);
            } elseif ($niveauCodification === 'basse') {
                $codification->setCodificationBasse($formation);
            } else {
                $codification->setCodificationFormation($formation);
            }

            $io->success('Formation '. $formation->getDisplay().' codifiée avec succès');
        }

        return Command::SUCCESS;
    }
}
