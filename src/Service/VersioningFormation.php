<?php

namespace App\Service;

use App\Entity\Formation;
use App\Entity\FormationVersioning;
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

class VersioningFormation {
    
    private Serializer $serializer;
    private EntityManagerInterface $entityManager;
    private Filesystem $filesystem;

    public function __construct(
        EntityManagerInterface $entityManager,
        Filesystem $filesystem
    ){
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
        // Serializer
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->serializer = new Serializer(
            [
                new BackedEnumNormalizer(),
                new DateTimeNormalizer(),
                new ArrayDenormalizer(),
                new ObjectNormalizer($classMetadataFactory, propertyTypeExtractor: new ReflectionExtractor())
            ],
            [
                new JsonEncoder()
            ]
        );
    }

    public function saveVersionOfFormation(Formation $formation, DateTimeImmutable $now, bool $withFlush = false){
        $dateHeure = $now->format('d-m-Y_H-i-s');
        // Nom du fichier
        $formationFilename = "formation-{$formation->getId()}-{$dateHeure}";
        // Objet BD Formation Versioning
        $formationVersioning = new FormationVersioning();
        $formationVersioning->setFormation($formation);
        $formationVersioning->setVersionTimestamp($now);
        $formationVersioning->setFilename($formationFilename);
        $formationVersioning->setSlug($formation->getSlug());
        // Serialization
        $formationJSON = $this->serializer->serialize($formation, 'json', [
            AbstractObjectNormalizer::GROUPS => ['formation_json_versioning'],
            'circular_reference_limit' => 2,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s',
        ]);
        // Enregistrement du fichier
        $this->filesystem->appendToFile(
            __DIR__ . "/../../versioning_json/formation/{$formation->getSlug()}/{$formationFilename}.json", 
            $formationJSON
        );
        $this->entityManager->persist($formationVersioning);
        if($withFlush){
            $this->entityManager->flush();
        }
    }

    public function loadFormationFromVersion(FormationVersioning $formationVersioning){
        $version = file_get_contents(
            __DIR__ . "/../../versioning_json/formation/{$formationVersioning->getSlug()}/"
            . "{$formationVersioning->getFilename()}.json");

        return $this->serializer->deserialize($version, Formation::class, 'json');
    }

    public function getDifferencesBetweenFormationAndLastVersion(Formation $formation){
        $formationTextDifferences = [];
        $lastVersion = $this->entityManager->getRepository(FormationVersioning::class)->findBy(
            ['formation' => $formation], 
            ['version_timestamp' => 'DESC']
        );
        $lastVersion = count($lastVersion) > 0 ? $lastVersion[0] : null;

        if($lastVersion){
            $lastVersion = $this->loadFormationFromVersion($lastVersion);
            // Configuration du calcul des différences
            $rendererName = 'Combined';
            $differOptions = [
                'context' => 1,
                'ignoreWhitespace' => true,
                'ignoreLineEnding' => true,
                'lengthLimit' => 4500
            ];
            // Affichage 
            $rendererOptions = [
                'detailLevel' => 'word',
                'lineNumbers' => false,
                'showHeader' => false,
                'separateBlock' => false,
            ];

            $localisationVersion = implode(", ", array_map(
                fn($ville) => $ville->getLibelle(), $lastVersion->getLocalisationMention()->toArray()
            ));
            $localisationActuelle = implode(", ", array_map(
                fn($ville) => $ville->getLibelle(), $formation->getLocalisationMention()->toArray()
            ));

            $formationTextDifferences = [
                "responsableDeFormation" => html_entity_decode(DiffHelper::calculate(
                    // Version
                    ($lastVersion->getResponsableMention()->getNom() ?? ""
                     . " " . 
                     $lastVersion->getResponsableMention()->getPrenom() ?? ""
                    ) ,
                    // Actuel
                    ($formation->getResponsableMention()->getNom() ?? ""
                     . " " . 
                     $formation->getResponsableMention()->getPrenom() ?? ""
                    ),
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
                "emailResponsableFormation" => html_entity_decode(DiffHelper::calculate(
                    // Version
                    $lastVersion->getResponsableMention()->getEmail() ?? "",
                    // Actuel
                    $formation->getResponsableMention()->getEmail() ?? "",
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
                "localisationMention" => html_entity_decode(DiffHelper::calculate(
                    // Version
                    $localisationVersion,
                    // Actuel
                    $localisationActuelle,
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                ))
            ];
        }

        return $formationTextDifferences;
    }
}