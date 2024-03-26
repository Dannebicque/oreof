<?php

namespace App\Service;

use App\DTO\StructureParcours;
use App\Entity\Parcours;
use App\Entity\ParcoursVersioning;
use App\TypeDiplome\TypeDiplomeRegistry;
use DateTimeImmutable;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Jfcherng\Diff\DiffHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class VersioningParcours {

    private EntityManagerInterface $entityManager;
    private Serializer $serializer;
    private Filesystem $fileSystem;
    private TypeDiplomeRegistry $typeD;

    public function __construct(
        EntityManagerInterface $entityManager,
        Filesystem $fileSystem,
        TypeDiplomeRegistry $typeD
    ){  
        $this->entityManager = $entityManager;
        $this->typeD = $typeD;
        $this->fileSystem = $fileSystem;
        // Définition du serializer
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->serializer = new Serializer(
        [
            new DateTimeNormalizer(),
            new BackedEnumNormalizer(),
            new ArrayDenormalizer(),
            new ObjectNormalizer($classMetadataFactory, propertyTypeExtractor: new ReflectionExtractor())
        ],
            [new JsonEncoder()]
        );
    }

    public function saveVersionOfParcours(Parcours $parcours, DateTimeImmutable $now, bool $withFlush = false){
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
        $parcoursJson = $this->serializer->serialize($parcours, 'json', [
            AbstractObjectNormalizer::GROUPS => ['parcours_json_versioning'],
            'circular_reference_limit' => 2,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s',
        ]);
        // DTO
        $typeD = $this->typeD->getTypeDiplome($parcours->getFormation()?->getTypeDiplome()?->getModeleMcc());
        $dto = $typeD->calculStructureParcours($parcours);
        $dtoJson = $this->serializer->serialize($dto, 'json', [
            AbstractObjectNormalizer::GROUPS => ['DTO_json_versioning'],
            'circular_reference_limit' => 2,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s',
        ]);
        // Enregistrement dans un fichier
        $this->fileSystem->appendToFile(__DIR__ . "/../../versioning_json/parcours/{$parcours->getId()}/{$parcoursFileName}.json", $parcoursJson);
        $this->fileSystem->appendToFile(__DIR__ . "/../../versioning_json/parcours/{$parcours->getId()}/{$dtoFileName}.json", $dtoJson);
        // Enregistrement de la référence en BD
        $this->entityManager->persist($parcoursVersioning);
        if($withFlush){
            $this->entityManager->flush();
        }
    }

    public function loadParcoursFromVersion(ParcoursVersioning $parcours_versioning){
        $fileParcours = file_get_contents(
            __DIR__ . "/../../versioning_json/parcours/"
                                    . "{$parcours_versioning->getParcours()->getId()}/"
                                    . "{$parcours_versioning->getParcoursFileName()}.json"
        );
        $fileDTO = file_get_contents(
            __DIR__ . "/../../versioning_json/parcours/"
                                    . "{$parcours_versioning->getParcours()->getId()}/"
                                    . "{$parcours_versioning->getDtoFileName()}.json"
        );
        $parcours = $this->serializer->deserialize($fileParcours, Parcours::class, 'json');
        $dto = $this->serializer->deserialize($fileDTO, StructureParcours::class, 'json');
        $dateVersion = $parcours_versioning->getVersionTimestamp()->format('d-m-Y à H:i');

        return [
            'parcours' => $parcours,
            'dto' => $dto,
            'dateVersion' => $dateVersion
        ];
    }

    public function getDifferencesBetweenParcoursAndLastVersion(Parcours $parcours){
        $textDifferences = [];
        $lastVersion = $this->entityManager->getRepository(ParcoursVersioning::class)->findLastVersion($parcours);
        $lastVersion = count($lastVersion) > 0 ? $lastVersion[0] : null;

        if($lastVersion){
            // Configuration du calcul des différences
            $rendererName = 'Combined';
            $differOptions = [
                'context' => 1,
                'ignoreWhitespace' => true,
                'ignoreLineEnding' => true,
            ];
            // Affichage 
            $rendererOptions = [
                'detailLevel' => 'word',
                'lineNumbers' => false,
                'showHeader' => false,
                'separateBlock' => false,
            ];
            // Données du parcours en JSON
            $fileParcours = file_get_contents(__DIR__ . "/../../versioning_json/parcours/"
                                            . "{$lastVersion->getParcours()->getId()}/"
                                            . "{$lastVersion->getParcoursFileName()}.json"
                            );
            $lastVersion = $this->serializer->deserialize($fileParcours, Parcours::class, 'json');
            $textDifferences = [
                'presentationParcoursContenuFormation' => html_entity_decode(DiffHelper::calculate(
                    $lastVersion->getContenuFormation() ?? "",
                    $parcours->getContenuFormation() ?? "",
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
                'presentationParcoursObjectifsParcours' => html_entity_decode(DiffHelper::calculate(
                    $lastVersion->getObjectifsParcours() ?? "",
                    $parcours->getObjectifsParcours() ?? "",
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
                'presentationParcoursResultatsAttendus' => html_entity_decode(DiffHelper::calculate(
                    $lastVersion->getResultatsAttendus() ?? "",
                    $parcours->getResultatsAttendus() ?? "",
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
                'presentationFormationObjectifsFormation' => html_entity_decode(DiffHelper::calculate(
                    $lastVersion->getFormation()?->getObjectifsFormation() ?? "",
                    $parcours->getFormation()?->getObjectifsFormation() ?? "",
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
            ];
        }

        return $textDifferences;
    }
}