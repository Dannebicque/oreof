<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Daeu/DaeuHandler.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 28/05/2025 15:26
 */

namespace App\TypeDiplome\M2E;

use App\DTO\StructureParcours;
use App\Entity\CampagneCollecte;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Entity\TypeEpreuve;
use App\Repository\BlocCompetenceRepository;
use App\Repository\TypeDiplomeRepository;
use App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException;
use App\TypeDiplome\M2E\Services\M2eMccc;
use App\TypeDiplome\M2E\Services\M2eMcccVersion;
use App\TypeDiplome\TypeDiplomeHandlerInterface;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class M2EHandler implements TypeDiplomeHandlerInterface
{

    public const TEMPLATE_FOLDER = 'm2e';
    public const SOURCE = 'm2e';
    public const TEMPLATE_FORM_MCCC = 'm2e.html.twig';

    private array $typeEpreuves;

    public function __construct(
        TypeDiplomeRepository            $typeDiplomeRepository,
        protected EntityManagerInterface $entityManager,
        protected M2eMccc                $m2eMccc,
        protected M2eMcccVersion         $m2eMcccVersion,
        private BlocCompetenceRepository $blocCompetenceRepository,
        private StructureParcoursM2e     $structureParcoursM2e
    )
    {
        $typeD = $typeDiplomeRepository->findOneBy(['libelle_court' => $this->getLibelleCourt()]);

        if ($typeD === null) {
            throw new TypeDiplomeNotFoundException();
        }

        $this->typeEpreuves = $this->entityManager->getRepository(TypeEpreuve::class)->findByTypeDiplome($typeD);
    }

    public function getTypeEpreuves(): array
    {
        return $this->typeEpreuves;
    }

    public function supports(string $type): bool
    {
        return $type === 'M2E';
    }

    public function exportExcelMccc(
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
        bool               $versionFull = true
    ): StreamedResponse
    {
        return $this->m2eMccc->exportExcelLicenceMccc($anneeUniversitaire, $parcours, $dateCfvu, $dateConseil, $versionFull);
    }

    public function exportExcelVersionMccc(
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
        bool               $versionFull = true
    ): StreamedResponse
    {
        return $this->m2eMcccVersion->exportExcelLicenceMccc($anneeUniversitaire, $parcours, $dateCfvu, $dateConseil, $versionFull);
    }

    public function exportExcelAndSaveVersionMccc(
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        string             $dir,
        string             $fichier,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null
    ): string
    {
        return $this->m2eMcccVersion->exportAndSaveExcelLicenceMccc($anneeUniversitaire, $parcours, $dir, $fichier, $dateCfvu, $dateConseil);
    }

    public function exportPdfMccc(
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
        bool               $versionFull = true
    ): Response
    {
        return $this->m2eMccc->exportPdfLicenceMccc($anneeUniversitaire, $parcours, $dateCfvu, $dateConseil, $versionFull);
    }

    public function exportAndSaveExcelMccc(
        string             $dir,
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
        bool               $versionFull = true
    ): string
    {
        return $this->m2eMccc->exportAndSaveExcelLicenceMccc($anneeUniversitaire, $parcours, $dir, $dateCfvu, $dateConseil, $versionFull);
    }

    public function exportAndSavePdfMccc(
        string             $dir,
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
        bool               $versionFull = true
    ): string
    {
        return $this->m2eMccc->exportAndSavePdfLicenceMccc($anneeUniversitaire, $parcours, $dir, $dateCfvu, $dateConseil, $versionFull);
    }

    public function calculStructureParcours(Parcours $parcours, bool $withEcts = true, bool $withBcc = true): StructureParcours
    {
        return new StructureParcours();
    }

    public function showStructure(Parcours $parcours): array
    {
        // TODO: Implement showStructure() method.
        return [];
    }

    public function getStructureCompetences(Parcours $parcours): array
    {
        return $this->blocCompetenceRepository->findByParcours($parcours);
    }

    public function clearMcccs(ElementConstitutif|FicheMatiere $objet): void
    {
        // TODO: Implement clearMcccs() method.
    }

    public function getMcccs(ElementConstitutif|FicheMatiere $elementConstitutif): array|Collection
    {
        // TODO: Implement getMcccs() method.
        return [];
    }

    public function saveMcccs(ElementConstitutif|FicheMatiere $elementConstitutif, InputBag $request): void
    {
        // TODO: Implement saveMcccs() method.
    }

    public function getLibelleCourt(): string
    {
        return 'M2E';
    }
}
