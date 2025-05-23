<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Source/ButTypeDiplome.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/03/2023 13:41
 */

namespace App\TypeDiplome\Source;

use App\Classes\CalculButStructureParcours;
use App\DTO\StructureParcours;
use App\Entity\CampagneCollecte;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Mccc;
use App\Entity\Parcours;
use App\Repository\ButCompetenceRepository;
use App\TypeDiplome\Export\ButMccc;
use App\TypeDiplome\Export\ButMcccVersion;
use App\TypeDiplome\Synchronisation\But;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\Tools;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ButTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    //pourcentage_{{ prefix }}_{{ ep }}
    //nombre_{{ prefix }}_{{ ep }}
    public const SOURCE = 'but';
    public const TEMPLATE_FORM_MCCC = 'but.html.twig';

    public string $libelle = 'Bachelor Universitaire de Technologie (B.U.T.)';

    private array $typeEpreuves = [
        'sae' => [
            'iut_portfolio', 'iut_livrable', 'iut_rapport', 'iut_soutenance',
            'hors_iut_entreprise', 'hors_iut_rapport', 'hors_iut_soutenance'
        ],
        'ressource' => [
            'td_tp_oral', 'td_tp_ecrit', 'td_tp_rapport', 'td_tp_autre',
            'cm_ecrit', 'cm_rapport'
        ]
    ];

    public function __construct(
        protected CalculButStructureParcours $calculStructureParcours,
        protected ButCompetenceRepository $butCompetenceRepository,
        protected EntityManagerInterface  $entityManager,
        protected TypeDiplomeRegistry     $typeDiplomeRegistry,
        protected But                     $synchronisationBut,
        protected ButMccc                 $butMccc,
        protected ButMcccVersion          $butMcccVersion
    ) {
        parent::__construct($entityManager, $typeDiplomeRegistry);
    }

    public function calculStructureParcours(Parcours $parcours): StructureParcours
    {
        return $this->calculStructureParcours->calcul($parcours);
    }

    public function getMcccs(ElementConstitutif|FicheMatiere $elementConstitutif): array|Collection
    {
        $mcccs = $elementConstitutif->getMcccs();
        $tab = [];
        foreach ($mcccs as $mccc) {
            $tab[$mccc->getTypeEpreuve()[0]] = $mccc;
        }

        return $tab;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function synchroniser(
        Formation $formation
    ): bool {
        return $this->synchronisationBut->synchroniser($formation);
    }

    public function synchroniserMccc(
        Formation $formation
    ): bool {
        $this->synchronisationBut->synchroniserMccc($formation);
        return true;
    }

    public function getRefCompetences(Parcours $parcours): array|Collection
    {
        return $this->butCompetenceRepository->findBy([
            'formation' => $parcours->getFormation(),
        ]);
    }

    public function saveMcccs(FicheMatiere $ficheMatiere, InputBag $request): void
    {
        if ($request->has('sansNote') && $request->get('sansNote') === 'on') {
            $ficheMatiere->setSansNote(true);
            $ficheMatiere->setEtatMccc('Complet');
        } else {
            $ficheMatiere->setSansNote(false);
            $type = $ficheMatiere->getTypeMatiere();
            $total = 0.0;
            $mcccs = $this->getMcccs($ficheMatiere);
            foreach ($this->typeEpreuves[$type] as $ep) {
                if ($request->has('pourcentage_' . $ep) && $request->has('nombre_' . $ep)) {
                    $pourcentage = $request->get('pourcentage_' . $ep);
                    $nombre = $request->get('nombre_' . $ep);
                    if (array_key_exists($ep, $mcccs)) {
                        $mcccs[$ep]->setPourcentage(Tools::convertToFloat($pourcentage));
                        $mcccs[$ep]->setNbEpreuves((int)$nombre);
                        $mcccs[$ep]->setLibelle($ep);
                        $mcccs[$ep]->setNumeroSession(1);
                        $mcccs[$ep]->setControleContinu(true);
                        $mcccs[$ep]->setExamenTerminal(false);
                        $total += $mcccs[$ep]->getPourcentage() * $mcccs[$ep]->getNbEpreuves();
                    } else {
                        $mccc = new Mccc();
                        $mccc->setTypeEpreuve([$ep]);
                        $mccc->setPourcentage(Tools::convertToFloat($pourcentage));
                        $mccc->setNbEpreuves((int)$nombre);
                        $mccc->setLibelle($ep);
                        $mccc->setControleContinu(true);
                        $mccc->setNumeroSession(1);
                        $mccc->setExamenTerminal(false);
                        $this->entityManager->persist($mccc);
                        $ficheMatiere->addMccc($mccc);
                        $total += $mccc->getPourcentage() * $mccc->getNbEpreuves();
                    }
                }
            }
            $ficheMatiere->setEtatMccc($total >= 99.0 ? 'Complet' : 'Incomplet');
        }

        $this->entityManager->flush();
    }

    public function exportExcelMccc(
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
        bool               $versionFull = true
    ): StreamedResponse {
        return $this->butMccc->exportExcelButMccc($anneeUniversitaire, $parcours, $dateCfvu, $dateConseil, $versionFull);
    }

    public function exportExcelVersionMccc(
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
        bool               $versionFull = true
    ): StreamedResponse {
        return $this->butMcccVersion->exportExcelButMccc($anneeUniversitaire, $parcours, $dateCfvu, $dateConseil, $versionFull);
    }

    public function exportPdfMccc(
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
        bool               $versionFull = true
    ): Response {
        return $this->butMccc->exportPdfButMccc($anneeUniversitaire, $parcours, $dateCfvu, $dateConseil, $versionFull);
    }

    public function exportAndSaveExcelMccc(
        string             $dir,
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
        bool               $versionFull = true
    ): string {
        return $this->butMccc->exportAndSaveExcelbutMccc($anneeUniversitaire, $parcours, $dir, $dateCfvu, $dateConseil, $versionFull);
    }

    public function exportExcelAndSaveVersionMccc(
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        string $dir,
        string $fichier,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
    ): string {
        return $this->butMcccVersion->exportAndSaveExcelbutMccc($anneeUniversitaire, $parcours, $dir, $dateCfvu, $dateConseil, false);
    }
}
