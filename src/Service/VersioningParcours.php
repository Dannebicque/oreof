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

    public function saveVersionOfParcours(Parcours $parcours, DateTimeImmutable $now, bool $withFlush = false, bool $isCfvu = false){
        $dateHeure = $now->format('d-m-Y_H-i-s');
        // Objet BD Parcours Versioning
        $parcoursVersioning = new ParcoursVersioning();
        $parcoursVersioning->setParcours($parcours);
        $parcoursVersioning->setVersionTimestamp($now);
        if($isCfvu){
            $parcoursVersioning->setCvfuFlag(true);
        }
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
                'lengthLimit' => 4500
            ];
            // Affichage 
            $rendererOptions = [
                'detailLevel' => 'word',
                'lineNumbers' => false,
                'showHeader' => false,
                'separateBlock' => false,
                'wordGlues' => [' ', '.']
            ];
            // Données du parcours en JSON
            $fileParcours = file_get_contents(__DIR__ . "/../../versioning_json/parcours/"
                                            . "{$lastVersion->getParcours()->getId()}/"
                                            . "{$lastVersion->getParcoursFileName()}.json"
                            );
            $lastVersion = $this->serializer->deserialize($fileParcours, Parcours::class, 'json');
            $textDifferences = [
                'presentationParcoursContenuFormation' => 
                self::cleanUpComparison(
                    html_entity_decode(DiffHelper::calculate(
                        self::cleanUpHtmlTextForComparison($lastVersion->getContenuFormation() ?? ""),
                        self::cleanUpHtmlTextForComparison($parcours->getContenuFormation() ?? ""),
                        $rendererName,
                        $differOptions,
                        $rendererOptions
                    ))
                ),
                'presentationParcoursObjectifsParcours' => 
                self::cleanUpComparison(
                    html_entity_decode(DiffHelper::calculate(
                        self::cleanUpHtmlTextForComparison($lastVersion->getObjectifsParcours() ?? ""),
                        self::cleanUpHtmlTextForComparison($parcours->getObjectifsParcours() ?? ""),
                        $rendererName,
                        $differOptions,
                        $rendererOptions
                    ))
                ),
                'presentationParcoursResultatsAttendus' => 
                self::cleanUpComparison(
                    html_entity_decode(DiffHelper::calculate(
                        self::cleanUpHtmlTextForComparison($lastVersion->getResultatsAttendus() ?? ""),
                        self::cleanUpHtmlTextForComparison($parcours->getResultatsAttendus() ?? ""),
                        $rendererName,
                        $differOptions,
                        $rendererOptions
                    ))
                ),
                'contactsParcoursResponsableParcours' => html_entity_decode(DiffHelper::calculate(
                    // Version
                    $lastVersion->getRespParcours() ? 
                    ($lastVersion->getRespParcours()->getNom()
                     . " " . 
                     $lastVersion->getRespParcours()->getPrenom()
                    )  : "",
                    // Actuel
                    $parcours->getRespParcours() ? 
                    ($parcours->getRespParcours()->getNom()
                     . " " . 
                     $parcours->getRespParcours()->getPrenom()
                    ) : "",
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
                "emailParcoursResponsableParcours" => html_entity_decode(DiffHelper::calculate(
                    // Version
                    $lastVersion->getRespParcours()?->getEmail() ?? "",
                    // Actuel
                    $parcours->getRespParcours()?->getEmail() ?? "",
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
                'contactsParcoursCoResponsableDuParcours' => html_entity_decode(DiffHelper::calculate(
                    // Version
                    $lastVersion->getCoResponsable() ? 
                    ($lastVersion->getCoResponsable()->getNom()
                     . " " . 
                     $lastVersion->getCoResponsable()->getPrenom()
                    )  : "",
                    // Actuel
                    $parcours->getCoResponsable() ? 
                    ($parcours->getCoResponsable()->getNom()
                     . " " . 
                     $parcours->getCoResponsable()->getPrenom()
                    ) : "",
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
                "emailParcoursCoResponsableDuParcours" => html_entity_decode(DiffHelper::calculate(
                    // Version
                    $lastVersion->getCoResponsable()?->getEmail() ?? "",
                    // Actuel
                    $parcours->getCoResponsable()?->getEmail() ?? "",
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
                "regimeInscriptionParcours" => html_entity_decode(DiffHelper::calculate(
                    "<p class=\"list-item\">"
                    . implode("</p><p class=\"list-item\">", 
                            array_map(fn($regime) => $regime->value, 
                            $lastVersion->getRegimeInscription())
                    ) . "</p>",
                    "<p class=\"list-item\">"
                    . implode("</p><p class=\"list-item\">", 
                            array_map(fn($regime) => $regime->value, 
                            $parcours->getRegimeInscription())
                    ) . "</p>",
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
                "rythmeFormationParcours" => html_entity_decode(DiffHelper::calculate(
                    $lastVersion->getRythmeFormation()?->getLibelle() ?? "",
                    $parcours->getRythmeFormation()?->getLibelle() ?? "",
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
                "memoireTextParcours" => html_entity_decode(DiffHelper::calculate(
                    $lastVersion->getMemoireText() ?? "",
                    $parcours->getMemoireText() ?? "",
                    $rendererName,
                    $differOptions,
                    $rendererOptions
                )),
                "stageTextParcours" => 
                self::cleanUpComparison(
                    html_entity_decode(DiffHelper::calculate(
                        self::cleanUpHtmlTextForComparison($lastVersion->getStageText() ?? ""),
                        self::cleanUpHtmlTextForComparison($parcours->getStageText() ?? ""),
                        $rendererName,
                        $differOptions,
                        $rendererOptions
                    )
                )),
                "prerequisRecommandesParcours" => 
                self::cleanUpComparison(
                    html_entity_decode(DiffHelper::calculate(
                        self::cleanUpHtmlTextForComparison($lastVersion->getPrerequis() ?? ""),
                        self::cleanUpHtmlTextForComparison($parcours->getPrerequis() ?? ""),
                        $rendererName,
                        $differOptions,
                        $rendererOptions
                    )
                ))
            ];
        }

        return $textDifferences;
    }

    public static function cleanUpHtmlTextForComparison(string $html) : string {
        $cleaned = $html;
        $cleaned = preg_replace('/\<div\>/m', '', $cleaned);
        $cleaned = preg_replace('/\<\/div\>/m', '', $cleaned);
        $cleaned = preg_replace('/\<ul\>|\<\/ul\>/m', '', $cleaned);
        $cleaned = preg_replace('/\<li\>/m', '<p class="list-item"><span class="list-item-text">', $cleaned);
        $cleaned = preg_replace('/\<\/li\>/m', '</span></p>', $cleaned);
        $cleaned = preg_replace('/\<\!--block--\>/m', '', $cleaned); 
        $cleaned = preg_replace('/\<br\>/m', '', $cleaned);
        // $cleaned = preg_replace('/\>(.+)\<br\>/m', '<p>$1</p>', $cleaned);
        

        return $cleaned;
    }

    public static function removeBlockHtml(string $html) : string {
        return preg_replace('/\<\!--block--\>/m', '', $html);
    }

    public static function cleanUpComparison(string $htmlComparison) : string {
        return preg_replace(
            '/(\<del\>)(\<\/span\>\<\/p\>\<p class\="list-item"\>\<span class\="list-item-text"\>)/m', 
            "$2$1", 
            $htmlComparison
        );
    }
}