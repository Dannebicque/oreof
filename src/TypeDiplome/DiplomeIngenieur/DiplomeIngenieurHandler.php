<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Daeu/DaeuHandler.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 28/05/2025 15:26
 */

namespace App\TypeDiplome\DiplomeIngenieur;

use App\Entity\CampagneCollecte;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\TypeDiplome\TypeDiplomeHandlerInterface;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DiplomeIngenieurHandler implements TypeDiplomeHandlerInterface
{

    public const TEMPLATE_FOLDER = 'diplomeIngenieur';
    public const SOURCE = 'diplome_ingenieur';
    public const TEMPLATE_FORM_MCCC = 'di.html.twig';

    public function supports(string $type): bool
    {
        return $type === 'DI';
    }

    public function calculStructureParcours(Parcours $parcours, bool $withEcts = true, bool $withBcc = true): \App\DTO\StructureParcours
    {
        // TODO: Implement calculStructure() method.
    }

    public function showStructure(Parcours $parcours): array
    {
        // TODO: Implement showStructure() method.
    }

    public function getStructureCompetences(Parcours $parcours)
    {
        // TODO: Implement getStructureCompetences() method.
    }

    public function exportExcelMccc(CampagneCollecte $anneeUniversitaire, Parcours $parcours, ?DateTimeInterface $dateCfvu = null, ?DateTimeInterface $dateConseil = null, bool $versionFull = true): StreamedResponse
    {
        // TODO: Implement exportExcelMccc() method.
    }

    public function exportExcelVersionMccc(CampagneCollecte $anneeUniversitaire, Parcours $parcours, ?DateTimeInterface $dateCfvu = null, ?DateTimeInterface $dateConseil = null, bool $versionFull = true): StreamedResponse
    {
        // TODO: Implement exportExcelVersionMccc() method.
    }

    public function exportExcelAndSaveVersionMccc(CampagneCollecte $anneeUniversitaire, Parcours $parcours, string $dir, string $fichier, ?DateTimeInterface $dateCfvu = null, ?DateTimeInterface $dateConseil = null): string
    {
        // TODO: Implement exportExcelAndSaveVersionMccc() method.
    }

    public function exportPdfMccc(CampagneCollecte $anneeUniversitaire, Parcours $parcours, ?DateTimeInterface $dateCfvu = null, ?DateTimeInterface $dateConseil = null, bool $versionFull = true): Response
    {
        // TODO: Implement exportPdfMccc() method.
    }

    public function exportAndSaveExcelMccc(string $dir, CampagneCollecte $anneeUniversitaire, Parcours $parcours, ?DateTimeInterface $dateCfvu = null, ?DateTimeInterface $dateConseil = null, bool $versionFull = true): string
    {
        // TODO: Implement exportAndSaveExcelMccc() method.
    }

    public function exportAndSavePdfMccc(string $dir, CampagneCollecte $anneeUniversitaire, Parcours $parcours, ?DateTimeInterface $dateCfvu = null, ?DateTimeInterface $dateConseil = null, bool $versionFull = true): string
    {
        // TODO: Implement exportAndSavePdfMccc() method.
    }

    public function clearMcccs(ElementConstitutif|FicheMatiere $objet): void
    {
        // TODO: Implement clearMcccs() method.
    }

    public function getMcccs(ElementConstitutif|FicheMatiere $elementConstitutif): array|Collection
    {
        // TODO: Implement getMcccs() method.
    }

    public function saveMcccs(ElementConstitutif|FicheMatiere $elementConstitutif, InputBag $request): void
    {
        // TODO: Implement saveMcccs() method.
    }

    public function getTypeEpreuves(): array
    {
        // TODO: Implement getTypeEpreuves() method.
    }
}
