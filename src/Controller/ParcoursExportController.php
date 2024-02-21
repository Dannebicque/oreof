<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FormationExportController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\CalculStructureParcours;
use App\Classes\MyGotenbergPdf;
use App\DTO\StructureEc;
use App\DTO\StructureUe;
use App\Entity\Parcours;
use App\Repository\TypeEpreuveRepository;
use App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException;
use App\Utils\CleanTexte;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ParcoursExportController extends AbstractController
{
    public function __construct(
        private readonly MyGotenbergPdf $myPdf
    ) {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws TypeDiplomeNotFoundException
     * @throws Exception
     */
    #[Route('/parcours/{parcours}/export-pdf', name: 'app_parcours_export')]
    public function export(
        Parcours                $parcours,
        CalculStructureParcours $calculStructureParcours
    ): Response {
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        if (null === $typeDiplome) {
            throw new Exception('Type de diplôme non trouvé');
        }

        return $this->myPdf->render('pdf/parcours.html.twig', [
            'formation' => $parcours->getFormation(),
            'typeDiplome' => $typeDiplome,
            'parcours' => $parcours,
            'hasParcours' => $parcours->getFormation()?->isHasParcours(),
            'titre' => 'Détails du parcours ' . $parcours->getDisplay(),
            'dto' => $calculStructureParcours->calcul($parcours)
        ], 'Parcours_' . $parcours->getDisplay());
    }

    #[Route('/parcours/{parcours}/maquette/export-json', name: 'app_parcours_export_maquette_json')]
    public function exportMaquetteJson(
        TypeEpreuveRepository    $typeEpreuveRepository,
        Parcours                $parcours,
        CalculStructureParcours $calculStructureParcours
    ): Response {
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        if (null === $typeDiplome) {
            throw new Exception('Type de diplôme non trouvé');
        }

        $dto = $calculStructureParcours->calcul($parcours);

        $data = [
            'path' => $this->generateUrl('app_parcours_export_maquette_json', ['parcours' => $parcours->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'id' => $parcours->getId(),
            'formationId' => $parcours->getFormation()?->getId(),
            'volumes'=> [
                'CM'=> [
                    'presentiel'=> $dto->heuresEctsFormation->sommeFormationCmPres,
                    'distanciel'=> $dto->heuresEctsFormation->sommeFormationCmDist
                ],
                'TD'=> [
                    'presentiel'=> $dto->heuresEctsFormation->sommeFormationTdPres,
                    'distanciel'=> $dto->heuresEctsFormation->sommeFormationTdDist
                ],
                'TP'=> [
                    'presentiel'=> $dto->heuresEctsFormation->sommeFormationTpPres,
                    'distanciel'=> $dto->heuresEctsFormation->sommeFormationTpDist
                ],
                'autonomie'=> $dto->heuresEctsFormation->sommeFormationTePres
            ],
            'ects' => $dto->heuresEctsFormation->sommeFormationEcts,
            'semestres' => []
        ];

        foreach ($dto->semestres as $ordre => $sem) {
            $semestre =  [
                'ordre' => $ordre,
                'volumes'=> [
                    'CM'=> [
                        'presentiel'=> $sem->heuresEctsSemestre->sommeSemestreCmPres,
                        'distanciel'=> $sem->heuresEctsSemestre->sommeSemestreCmDist
                    ],
                    'TD'=> [
                        'presentiel'=> $sem->heuresEctsSemestre->sommeSemestreTdPres,
                        'distanciel'=> $sem->heuresEctsSemestre->sommeSemestreTdDist
                    ],
                    'TP'=> [
                        'presentiel'=> $sem->heuresEctsSemestre->sommeSemestreTpPres,
                        'distanciel'=> $sem->heuresEctsSemestre->sommeSemestreTpDist
                    ],
                    'autonomie'=> $sem->heuresEctsSemestre->sommeSemestreTePres
                ],
                'ects' => $sem->heuresEctsSemestre->sommeSemestreEcts,
                'ues' => []
            ];
            foreach ($sem->ues() as $ue) {
                $tUe = [
                    'ordre' => $ue->ordre(),
                    'libelleOrdre' => $ue->display,
                    'libelle' => $ue->ue->getLibelle() ?? $ue->display,
                    'volumes'=> [
                        'CM'=> [
                            'presentiel'=> $ue->heuresEctsUe->sommeUeCmPres,
                            'distanciel'=> $ue->heuresEctsUe->sommeUeCmDist
                        ],
                        'TD'=> [
                            'presentiel'=> $ue->heuresEctsUe->sommeUeTdPres,
                            'distanciel'=> $ue->heuresEctsUe->sommeUeTdDist
                        ],
                        'TP'=> [
                            'presentiel'=> $ue->heuresEctsUe->sommeUeTpPres,
                            'distanciel'=> $ue->heuresEctsUe->sommeUeTpDist
                        ],
                        'autonomie'=> $ue->heuresEctsUe->sommeUeTePres
                    ],
                    'ects' => $ue->heuresEctsUe->sommeUeEcts,
                ];

                if ($ue->ue->getNatureUeEc()?->isLibre()) {
                    $tUe['ects'] = $ue->ue->getEcts() ?? 0.0;
                    $tUe['description_libre_choix'] = $ue->ue->getDescriptionUeLibre();
                } elseif ($ue->ue->getNatureUeEc()?->isChoix()) {
                    $tUe['description_libre_choix'] = $ue->ue->getDescriptionUeLibre();
                    $tUe['UesEnfants'] = [];
                    $nb = 0;
                    foreach ($ue->uesEnfants() as $ueEnfant) {
                        $tUeEnfant = [
                            'ordre' => $ueEnfant->ordre(),
                            'libelleOrdre' => $ueEnfant->display,
                            'libelle' => $ueEnfant->ue->getLibelle() ?? $ueEnfant->display,
                            'volumes'=> [
                                'CM'=> [
                                    'presentiel'=> $ueEnfant->heuresEctsUe->sommeUeCmPres,
                                    'distanciel'=> $ueEnfant->heuresEctsUe->sommeUeCmDist
                                ],
                                'TD'=> [
                                    'presentiel'=> $ueEnfant->heuresEctsUe->sommeUeTdPres,
                                    'distanciel'=> $ueEnfant->heuresEctsUe->sommeUeTdDist
                                ],
                                'TP'=> [
                                    'presentiel'=> $ueEnfant->heuresEctsUe->sommeUeTpPres,
                                    'distanciel'=> $ueEnfant->heuresEctsUe->sommeUeTpDist
                                ],
                                'autonomie'=> $ueEnfant->heuresEctsUe->sommeUeTePres
                            ],
                            'ects' => $ueEnfant->heuresEctsUe->sommeUeEcts,

                        ];
                        $nb++;
                        $tUe['nbChoix'] = $nb;
                        $tUeEnfant['ec'] = $this->getEcFromUe($ueEnfant);
                        $tUe['UesEnfants'][] = $tUeEnfant;
                    }
                } else {
                    $tUe['ects'] = $ue->heuresEctsUe->sommeUeEcts;
                    $tUe['ec'] = $this->getEcFromUe($ue);
                }
                $semestre['ues'][] = $tUe;
            }

            $data['semestres'][] = $semestre;
        }

        return $this->json($data);
    }

    private function getEcFromUe(StructureUe $ue): array
    {
        $tEcs = [];
        foreach ($ue->elementConstitutifs as $ec) {
            if ($ec->elementConstitutif->getNatureUeEc()?->isLibre()) {
                $tEcs['description_libre_choix'] =  $ec->elementConstitutif->gettexteEcLibre();
            } elseif ($ec->elementConstitutif->getNatureUeEc()?->isChoix()) {
                $tEc['ordre'] = $ec->elementConstitutif->getOrdre();
                $tEc['numero'] = $ec->elementConstitutif->getCode();
                $tEc['libelle'] = $ec->elementConstitutif?->getFicheMatiere()?->getLibelle() ?? '-';
                $tEc['ecsEnfants'] =  [];
                $tEc['description_libre_choix'] =  $ec->elementConstitutif->gettexteEcLibre();
                $nb = 0;
                foreach ($ec->elementsConstitutifsEnfants as $ecEnfant) {
                    $tEc['ecsEnfants'][] = $this->getEc($ecEnfant);
                    $nb++;
                }
                $tEc['nbChoix'] =  $nb;

                $tEcs[] = $tEc;
            } else {
                $tEcs[] = $this->getEc($ec);
            }
        }
        return $tEcs;
    }

    private function getEc(StructureEc $ec): array
    {
        if ($ec->elementConstitutif->getFicheMatiere() !== null &&
            (array_key_exists('publie', $ec->elementConstitutif->getFicheMatiere()->getEtatFiche()) ||
            array_key_exists('valide_pour_publication', $ec->elementConstitutif->getFicheMatiere()->getEtatFiche()))
        ) {
            $valide = true;
        } else {
            $valide = false;
        }

        return [
            'ordre' => $ec->elementConstitutif->getOrdre(),
            'valide' => $valide,
            'nature_ec' => $ec->elementConstitutif->getTypeEc()?->getLibelle(),
            'valide_date' => new DateTime(),
            'numero'=> $ec->elementConstitutif->getCode(),
            'libelle'=> $ec->elementConstitutif->getFicheMatiere()?->getLibelle() ?? '-',
            'libelle_anglais' => $ec->elementConstitutif->getFicheMatiere()?->getLibelleAnglais() ?? '-',
            'sigle'=> $ec->elementConstitutif->getFicheMatiere()?->getSigle() ?? '-', "",
            'enseignant_referent' => [
                'nom'=> $ec->elementConstitutif->getFicheMatiere()?->getResponsableFicheMatiere()?->getDisplay() ?? '-',
                'email'=> $ec->elementConstitutif->getFicheMatiere()?->getResponsableFicheMatiere()?->getEmail() ?? '-'
            ],
            'description' => CleanTexte::cleanTextArea($ec->elementConstitutif->getFicheMatiere()?->getDescription()) ?? '-',
            'objectifs' => CleanTexte::cleanTextArea($ec->elementConstitutif->getFicheMatiere()?->getObjectifs()) ?? '-',
            'modalite_enseignement'=> $ec->elementConstitutif->getFicheMatiere()?->getModaliteEnseignement()->value ?? '-',
            'langues_supports' => $ec->elementConstitutif->getFicheMatiere()?->getLanguesSupportsArray() ?? [],
            'langues_dispense_cours' => $ec->elementConstitutif->getFicheMatiere()?->getLanguesDispenseArray() ?? [],
            'ects'=> $ec->heuresEctsEc->ects,
            'volumes'=> [
                'CM'=> [
                    'presentiel'=> $ec->heuresEctsEc->cmPres,
                    'distanciel'=> $ec->heuresEctsEc->cmDist
                ],
                'TD'=> [
                    'presentiel'=> $ec->heuresEctsEc->tdPres,
                    'distanciel'=> $ec->heuresEctsEc->tdDist
                ],
                'TP'=> [
                    'presentiel'=> $ec->heuresEctsEc->tpPres,
                    'distanciel'=> $ec->heuresEctsEc->tpDist
                ],
                'autonomie'=> $ec->heuresEctsEc->tePres
            ],
        ];
    }
}
