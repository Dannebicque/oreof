<?php

namespace App\Command;

use App\Entity\CampagneCollecte;
use App\Entity\Parcours;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use DateTimeImmutable;
use App\Entity\ParcoursVersioning;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[AsCommand(
    name: 'app:versioning-parcours',
    description: "Sauvegarde d'un ou plusieurs parcours au format JSON",
)]
class VersioningParcoursCommand extends Command
{

    private EntityManagerInterface $entityManager;
    private Filesystem $filesystem;
    private TypeDiplomeRegistry $typeDiplomeRegistry;

    public function __construct(
        EntityManagerInterface $entityManager,
        Filesystem $filesystem,
        TypeDiplomeRegistry $typeDiplomeRegistry
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
        $this->typeDiplomeRegistry = $typeDiplomeRegistry;
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'full-database', 
            mode: InputOption::VALUE_NONE,
            description: 'Sauvegarde tous les parcours de la base de données en JSON'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fullDatabase = $input->getOption('full-database');

        if($fullDatabase){
            $io->writeln("Sauvegarde de tous les parcours en cours...");
            $dpe = $this->entityManager->getRepository(CampagneCollecte::class)->findOneBy(['defaut' => true]);
            $parcoursArray = $this->entityManager->getRepository(Parcours::class)->findParcours($dpe, []);
            $io->progressStart(count($parcoursArray));
            try{
                foreach($parcoursArray as $parcours){
                    $this->saveOneParcoursIntoJSON($parcours);
                    $io->progressAdvance();
                }
                $io->success('Sauvegarde en JSON des parcours de la base de données réussie.');
                return Command::SUCCESS;
            }catch(\Exception $e){
                $io->error("Une erreur est survenue : " . $e->getMessage());
            }
        }
        
        $io->warning("Option de la commande non reconnue. Choix possibles : ['full-database']");
        return Command::INVALID;
    }

    private function saveOneParcoursIntoJSON(Parcours $parcours){
        // Définition du serializer
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $serializer = new Serializer(
            [
                new DateTimeNormalizer(),
                new BackedEnumNormalizer(),
                new ObjectNormalizer($classMetadataFactory, propertyTypeExtractor: new ReflectionExtractor())
            ],
            [new JsonEncoder()]
        );
        $now = new DateTimeImmutable('now');
        $dateHeure = $now->format('d-m-Y_H-i-s');
        // Objet BD Parcours Versioning
        $parcoursVersioning = new ParcoursVersioning();
        $parcoursVersioning->setParcours($parcours);
        $parcoursVersioning->setVersionTimestamp($now);
        // Nom du fichier
        $parcoursFileName = "parcours-{$parcours->getId()}-{$dateHeure}";
        $dtoFileName = "dto-{$parcours->getId()}-{$dateHeure}";
        $parcoursVersioning->setParcoursFileName($parcoursFileName);
        $parcoursVersioning->setDtoFileName($dtoFileName);
        // Création du fichier JSON
        // Parcours
        $parcoursJson = $serializer->serialize($parcours, 'json', [
            AbstractObjectNormalizer::GROUPS => ['parcours_json_versioning'],
            'circular_reference_limit' => 2,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s',
        ]);
        // DTO
        $typeD = $this->typeDiplomeRegistry->getTypeDiplome($parcours->getFormation()?->getTypeDiplome()?->getModeleMcc());
        $dto = $typeD->calculStructureParcours($parcours);
        $dtoJson = $serializer->serialize($dto, 'json', [
            AbstractObjectNormalizer::GROUPS => ['DTO_json_versioning'],
            'circular_reference_limit' => 2,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s',
        ]);
        // Enregistrement dans un fichier
        $this->filesystem->appendToFile(__DIR__ . "/../../versioning_json/parcours/{$parcours->getId()}/{$parcoursFileName}.json", $parcoursJson);
        $this->filesystem->appendToFile(__DIR__ . "/../../versioning_json/parcours/{$parcours->getId()}/{$dtoFileName}.json", $dtoJson);
        // Enregistrement de la référence en BD
        $this->entityManager->persist($parcoursVersioning);
        $this->entityManager->flush();
    }
}
