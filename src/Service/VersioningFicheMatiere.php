<?php

namespace App\Service;

use App\Entity\FicheMatiere;
use App\Entity\FicheMatiereVersioning;
use DateTimeImmutable;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Jfcherng\Diff\DiffHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class VersioningFicheMatiere {

    private EntityManagerInterface $entityManager;
    private Serializer $serializer;
    private Filesystem $filesystem;

    public function __construct(
        EntityManagerInterface $entityManager,
        Filesystem $filesystem
    ){
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
        // Serializer
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $extractors = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);
        $this->serializer = new Serializer(
            [
                new DateTimeNormalizer(),
                new ArrayDenormalizer(),
                new ObjectNormalizer($classMetadataFactory, propertyTypeExtractor: $extractors)
            ],
            [new JsonEncoder()]
        );
    }

    public function saveFicheMatiereVersion(
        FicheMatiere $ficheMatiere, 
        DateTimeImmutable $now, 
        bool $withFlush = false,
        bool $isVersionValide = false,    
    ){
        $dateHeure = $now->format('d-m-Y_H-i-s');
        // Objet BD fiche matiere versioning
        $ficheMatiereVersioning = new FicheMatiereVersioning();
        $ficheMatiereVersioning->setFicheMatiere($ficheMatiere);
        $ficheMatiereVersioning->setVersionTimestamp($now);
        $ficheMatiereVersioning->setSlug($ficheMatiere->getSlug());
        // Fichier Json
        $ficheMatiereFileName = "fiche-matiere-{$ficheMatiere->getId()}-{$dateHeure}";
        $ficheMatiereVersioning->setFilename($ficheMatiereFileName);
        // Version validée
        if($isVersionValide === true){
            $ficheMatiereVersioning->setVersionValide(true);
        }
        // Serialization
        $ficheMatiereJson = $this->serializer->serialize($ficheMatiere, 'json', [
            AbstractObjectNormalizer::GROUPS => ['fiche_matiere_versioning', 'fiche_matiere_versioning_ec_parcours'],
            'circular_reference_limit' => 2,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'
        ]);
        // Enregistrement du fichier
        $this->filesystem->appendToFile(
            __DIR__ . "/../../versioning_json/fiche-matiere/{$ficheMatiereVersioning->getSlug()}/{$ficheMatiereFileName}.json",
            $ficheMatiereJson
        );
        // Enregistrement en BD
        $this->entityManager->persist($ficheMatiereVersioning);
        if($withFlush){
            $this->entityManager->flush();
        }
    }

    public function loadFicheMatiereVersion(FicheMatiereVersioning $ficheMatiereVersioning){
        $ficheMatiereJson = file_get_contents(
            __DIR__ . "/../../versioning_json/fiche-matiere/"
            . "{$ficheMatiereVersioning->getSlug()}/"
            . "{$ficheMatiereVersioning->getFilename()}.json"
        );
        $ficheMatiere = $this->serializer->deserialize($ficheMatiereJson, FicheMatiere::class, 'json');
        $dateVersion = $ficheMatiereVersioning->getVersionTimestamp()->format('d-m-Y à H:i');

        return [
            'ficheMatiere' => $ficheMatiere,
            'dateVersion' => $dateVersion
        ];
    }

    public function getStringDifferencesWithBetweenFicheMatiereAndLastVersion(
        FicheMatiere $ficheMatiere, 
        bool $lastVersionValide = true
    ){
        if($lastVersionValide){
            $lastVersion = $this->entityManager->getRepository(FicheMatiereVersioning::class)
                ->findBy(
                    ['ficheMatiere' => $ficheMatiere, 'version_valide' => true],
                    ['version_timestamp' => 'DESC']
                );
        }
        else {
            $lastVersion = $this->entityManager->getRepository(FicheMatiereVersioning::class)
                ->findBy(
                    ['ficheMatiere' => $ficheMatiere], 
                    ['version_timestamp' => 'DESC']
                );
        }
        $lastVersion = count($lastVersion) > 0 
            ? $this->loadFicheMatiereVersion($lastVersion[0])['ficheMatiere'] 
            : null;
        

        $textDifferences = [];

        $rendererName = 'Combined';
        $differOptions = [
            'context' => 1,
            'ignoreWhitespace' => true,
            'ignoreLineEnding' => true,
        ];
        $rendererOptions = [
            'detailLevel' => 'word',
            'lineNumbers' => false,
            'showHeader' => false,
            'separateBlock' => false,
        ];
        if($lastVersion){
            $textDifferences = [
                'descriptionEnseignement' => VersioningParcours::cleanUpComparison(
                    html_entity_decode(
                        DiffHelper::calculate(
                            VersioningParcours::cleanUpHtmlTextForComparison($lastVersion->getDescription() ?? ""),
                            VersioningParcours::cleanUpHtmlTextForComparison($ficheMatiere->getDescription() ?? ""),
                            $rendererName,
                            $differOptions,
                            $rendererOptions
                
                        )
                    )
                ),
                'objectifsEnseignement' => VersioningParcours::cleanUpComparison(
                    html_entity_decode(
                        DiffHelper::calculate(
                            VersioningParcours::cleanUpHtmlTextForComparison($lastVersion->getObjectifs() ?? ""),
                            VersioningParcours::cleanUpHtmlTextForComparison($ficheMatiere->getObjectifs() ?? ""),
                            $rendererName,
                            $differOptions,
                            $rendererOptions
                        )
                    )  
                ),
                'languesEnseignement' => VersioningParcours::cleanUpComparison(
                    html_entity_decode(
                        DiffHelper::calculate(
                            "<p class=\"list-item\">"
                            . implode(
                                "</p><p class=\"list-item\">",
                                array_map(
                                    fn ($langue) => $langue->getLibelle(),
                                    $lastVersion->getLangueDispense()->toArray()
                                )
                            ) . "</p>",
                            "<p class=\"list-item\">"
                            . implode(
                                "</p><p class=\"list-item\">",
                                array_map(
                                    fn ($langue) => $langue->getLibelle(),
                                    $ficheMatiere->getLangueDispense()->toArray()
                                )
                            ) . "</p>",
                            $rendererName,
                            $differOptions,
                            $rendererOptions
                        )
                    )
                ),
                'supportDeCoursEn' => VersioningParcours::cleanUpComparison(
                    html_entity_decode(
                        DiffHelper::calculate(
                            "<p class=\"list-item\">"
                            . implode(
                                "</p><p class=\"list-item\">",
                                array_map(
                                    fn ($langue) => $langue->getLibelle(),
                                    $lastVersion->getLangueSupport()->toArray()
                                )
                            ) . "</p>",
                            "<p class=\"list-item\">"
                            . implode(
                                "</p><p class=\"list-item\">",
                                array_map(
                                    fn ($langue) => $langue->getLibelle(),
                                    $ficheMatiere->getLangueSupport()->toArray()
                                )
                            ) . "</p>",
                            $rendererName,
                            $differOptions,
                            $rendererOptions
                        )
                    )
                ),
                'responsableEnseignement' => VersioningParcours::cleanUpComparison(
                    html_entity_decode(
                        DiffHelper::calculate(
                            $lastVersion->getResponsableFicheMatiere()->getNom() . " " . $lastVersion->getResponsableFicheMatiere()->getPrenom(),
                            $ficheMatiere->getResponsableFicheMatiere()->getNom() . " " . $ficheMatiere->getResponsableFicheMatiere()->getPrenom(),
                            $rendererName,
                            $differOptions,
                            $rendererOptions
                        )
                    )
                ),
                'emailResponsableEnseignement' => VersioningParcours::cleanUpComparison(
                    html_entity_decode(
                        DiffHelper::calculate(
                            $lastVersion->getResponsableFicheMatiere()->getEmail(),
                            $ficheMatiere->getResponsableFicheMatiere()->getEmail(),
                            $rendererName,
                            $differOptions,
                            $rendererOptions
                        )
                    )
                )
            ];

        }

        return $textDifferences;
    }

    private function getArrayDisplayAsList(array|Collection $array, string $keyIndex){
        $list = "<ul>";
        foreach($array as $value){
            $list .= "<li>" . $array[$keyIndex] . "</li>";
        }
        $list .= "</ul>";
        return $list;
    }
}