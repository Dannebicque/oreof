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
use App\Entity\Ue;
use App\Repository\TypeEpreuveRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;

class ParcoursExportController extends AbstractController
{
    /**
     * @var \App\Entity\TypeEpreuve[]
     */
    private array $typeEpreuves = [];

    public function __construct(
        private readonly MyGotenbergPdf $myPdf
    ) {
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/parcours/{parcours}/export-pdf', name: 'app_parcours_export')]
    public function export(
        Parcours                $parcours,
        CalculStructureParcours $calculStructureParcours
    ): Response {
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        if (null === $typeDiplome) {
            throw new \Exception('Type de diplôme non trouvé');
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
            throw new \Exception('Type de diplôme non trouvé');
        }

        $this->typeEpreuves = $typeEpreuveRepository->findByTypeDiplome($typeDiplome);

        $dto = $calculStructureParcours->calcul($parcours);

        /* /maquette
        {
            "method": "GET",
    "path": "/maquette",
    "parameters": [
        {
            "name": "id",
            "type": "string",
            "required": true
        }
    ],
    "response": {
            "id": "367",
        "formationId": "8762",
        "semestres": [
            {
                "numero": 1,
                "ec": [
                    {
                        "numero": "EC 2.B",
                        "libelle": "Cours fondamental et méthodologie en histoire Moderne",
                        "libelle_anglais": "",
                        "sigle": "",
                        "enseignant_referent": {
                        "nom": "Prof. Dupont",
                            "email": "dupont@example.com"
                        },
                        "description": "C'est un cours en histoire moderne",
                        "objectifs": "Les objectifs du cours",
                        "blocs_competences": [
                            {
                                "numero": 2,
                                "libelle": "Action en responsabilité au sein d'une organisation professionnelle",
                                "competences": [
                                    {
                                        "libelle": "Situer son rôle et sa mission au sein d'une organisation pour s’adapter et prendre des initiatives.",
                                        "numero": "2A"
                                    },
                                    {
                                        "libelle": "Respecter les principes d’éthique, de déontologie et de responsabilité environnementale.",
                                        "numero": "2B"
                                    }
                                ]
                            }
                        ],
                        "modalite_enseignement": "Présentiel",
                        "langues_supports": [
                        "Français",
                        "Anglais"
                    ],
                        "langues_dispense_cours": [
                        "Français"
                    ],
                        "volumes": {
                        "CM": {
                            "presentiel": 20,
                                "distanciel": 5
                            },
                            "TD": {
                            "presentiel": 15,
                                "distanciel": 10
                            },
                            "TP": {
                            "presentiel": 10,
                                "distanciel": 5
                            },
                            "autonomie": 5
                        },
                        "ects": 5,
                        "ressources": "Supports de cours et exercices",
                        "mccc": {
                        "premiere_session": {
                            "CC": [
                                "CC1 (50%); CC2 (50%)"
                            ],
                                "CT": [
                                "ET 04h00 (50%)"
                            ]
                            },
                            "seconde_chance": {
                            "CC_lt_10": {
                                "sans_tp": [
                                    "OT (100%)"
                                ],
                                    "avec_tp": null
                                },
                                "CC_gt_10": [
                                "CCr (50%)+ OT (50%)"
                            ],
                                "CT_only": [
                                "DO (100%)"
                            ]
                            }
                        }
                    }
                ]
            }
        ]
    }
}
**/

        $data = [
            "path" => $this->generateUrl('app_parcours_export_maquette_json', ['parcours' => $parcours->getId()], UrlGenerator::ABSOLUTE_URL),
            "id" => $parcours->getId(),
            'formationId' => $parcours->getFormation()?->getId(),
            'semestres' => []
        ];

        foreach ($dto->semestres as $ordre => $sem) {
            $semestre =  [
                'ordre' => $ordre,
                'ues' => []
            ];
            foreach ($sem->ues() as $ue) {
                $tUe = [
                    'ordre' => $ue->ordre(),
                    'libelleOrdre' => $ue->display,
                    'libelle' => $ue->ue->getLibelle() ?? $ue->display,
                ];

                if ($ue->ue->getNatureUeEc()?->isLibre()) {
                    $tUe['ects'] = $ue->ue->getEcts() ?? 0.0;
                    $tUe['description_libre_choix'] = $ue->ue->getDescriptionUeLibre();
                } elseif ($ue->ue->getNatureUeEc()?->isChoix()) {
                    $tUe['description_libre_choix'] = $ue->ue->getDescriptionUeLibre();
                    $tUe['UesEnfant'] = [];
                    foreach ($ue->uesEnfants() as $ueEnfant) {
                        $tUeEnfant = [
                            'ordre' => $ueEnfant->ordre(),
                            'libelleOrdre' => $ueEnfant->display,
                            'libelle' => $ueEnfant->ue->getLibelle() ?? $ueEnfant->display,
                        ];

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
                foreach ($ec->elementsConstitutifsEnfants as $ecEnfant) {
                    $tEc['ecsEnfants'][] = $this->getEc($ecEnfant);
                }
                $tEcs[] = $tEc;
            } else {
                $tEcs[] = $this->getEc($ec);
            }
        }
        return $tEcs;
    }

    private function getEc(StructureEc $ec): array
    {
        $tEc = [
            'ordre' => $ec->elementConstitutif->getOrdre(),
            "numero"=> $ec->elementConstitutif->getCode(),
            "libelle"=> $ec->elementConstitutif?->getFicheMatiere()?->getLibelle() ?? '-',
            "libelle_anglais" => $ec->elementConstitutif?->getFicheMatiere()?->getLibelleAnglais() ?? '-',
            "sigle"=> $ec->elementConstitutif?->getFicheMatiere()?->getSigle() ?? '-', "",
            "enseignant_referent" => [
                "nom"=> $ec->elementConstitutif?->getFicheMatiere()?->getResponsableFicheMatiere()?->getDisplay() ?? '-',
                "email"=> $ec->elementConstitutif?->getFicheMatiere()?->getResponsableFicheMatiere()?->getEmail() ?? '-'
            ],
            "description" => $ec->elementConstitutif?->getFicheMatiere()?->getDescription() ?? '-',
            "objectifs" => $ec->elementConstitutif?->getFicheMatiere()?->getObjectifs() ?? '-',
            "modalite_enseignement"=> $ec->elementConstitutif?->getFicheMatiere()?->getModaliteEnseignement()->value ?? '-',
            "langues_supports" => $ec->elementConstitutif?->getFicheMatiere()?->getLanguesSupportsArray() ?? [],
            "langues_dispense_cours" => $ec->elementConstitutif?->getFicheMatiere()?->getLanguesDispenseArray() ?? [],
            "ects"=> $ec->heuresEctsEc->ects,
            "volumes"=> [
                "CM"=> [
                    "presentiel"=> $ec->heuresEctsEc->cmPres,
                    "distanciel"=> $ec->heuresEctsEc->cmDist
                ],
                "TD"=> [
                    "presentiel"=> $ec->heuresEctsEc->tdPres,
                    "distanciel"=> $ec->heuresEctsEc->tdDist
                ],
                "TP"=> [
                    "presentiel"=> $ec->heuresEctsEc->tpPres,
                    "distanciel"=> $ec->heuresEctsEc->tpDist
                ],
                "autonomie"=> $ec->heuresEctsEc->tePres
            ],
            "mccc" => $this->getMccc($ec),
        ];

        return $tEc;
    }

    private function getMccc(StructureEc $ec): array
    {
        $tMcccs = [];
        $tMcccs['type_mccc'] = $ec->typeMccc;
        $tMcccs['mccc'] = [];
        foreach ($ec->mcccs as $mccc) {
            if (count($mccc->getTypeEpreuve()) === 1) {
                $typeE = $mccc->getTypeEpreuve()[0];
            } else {
                $typeE = '';
                foreach ($mccc->getTypeEpreuve() as $typeEpreuve) {
                    $typeE .= $typeEpreuve . ' / ';
                }
                $typeE = substr($typeE, 0, -3);
            }


            $tMccc = [
                'libelle' => $mccc->getLibelle(),
                'secondeChance' => $mccc->isSecondeChance(),
                'pourcentage' => $mccc->getPourcentage(),
                'nbEpreuves' => $mccc->getNbEpreuves(),
                'typeEpreuve' => $typeE,
                'controleContinu' => $mccc->isControleContinu(),
                'examenTerminal' => $mccc->isExamenTerminal(),
                'duree' => $mccc->getDuree(),
                'numeroEpreuve' => $mccc->getNumeroEpreuve(),
            ];

            if (array_key_exists($mccc->getNumeroSession(), $tMcccs['mccc'])) {
                $tMcccs['mccc'][$mccc->getNumeroSession()][] = $tMccc;
            } else {
                $tMcccs['mccc'][$mccc->getNumeroSession()] = [$tMccc];
            }
        }
        return $tMcccs;
    }
}
