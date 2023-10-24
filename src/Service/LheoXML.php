<?php

namespace App\Service;

use App\Entity\Parcours;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class LheoXML {
    /**
     * Génère du contenu XML de l'offre de formation, à partir d'un parcours.
     * Le format est généré selon les spécifications du LHEO
     * @param Parcours $parcours Parcours à transformer en XML
     * @return string $xml Contenu XML
     */
    public function generateLheoXMLFromParcours(Parcours $parcours) : string {
        // Paramètres de l'encodeur
        $contextOptions = [
            'xml_root_node_name' => 'lheo',
            'xml_format_output' => true,
            'xml_encoding' => 'utf-8'
        ];

        //Récupération des valeurs
        // Codes ROME
        $codesRome = [];
        foreach($parcours->getCodesRome() as $code){
            preg_match_all('/([A-Za-z][0-9]{4})/m', $code['code'], $matches);
            if(isset($matches[1][0])){
                $codesRome[] = $matches[1][0];
            }
        }
        // 5 codes ROME max
        $codesRome = array_slice($codesRome, 0, 5);

        // Intitulé de la formation
        $intituleFormation = $parcours->getFormation()->getTypeDiplome()->getLibelle() . " " . $parcours->getLibelle();

        // Rythme de la formation
        $rythmeFormation = 'Non renseigné.';
        if($parcours->getRythmeFormationTexte() !== null && !empty($parcours->getRythmeFormationTexte())){
            $rythmeFormation = $parcours->getRythmeFormationTexte();
        }
        else {
            if($parcours->getRythmeFormation() !== null && $parcours->getRythmeFormation()->getLibelle() !== null){
                $rythmeFormation = $parcours->getRythmeFormation()->getLibelle();
            }
        }

        // Génération du XML
        $encoder = new XmlEncoder();
        $xml = $encoder->encode([
            // Attribut de l'élément racine
            '@xmlns' => 'http://lheo.gouv.fr/2.3',
            '@xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            '@xsi:schemaLocation' => 'http://lheo.gouv.fr/2.3/lheo.xsd',
            'offres' => [ 
                'formation' => [
                    'domaine-formation' => [
                        // Formacode et code nsf optionnels
                        // 'code-FORMACODE' => '',
                        // 'code-NSF' => '',
                        'code-ROME' => $codesRome,
                    ],
                    'intitule-formation' => $this->cleanString($intituleFormation),
                    'objectif-formation' => $this->cleanString($parcours->getObjectifsParcours()),
                    'resultats-attendus' => $this->cleanString($parcours->getResultatsAttendus()),
                    'contenu-formation' => $this->cleanString($parcours->getContenuFormation()),
                    // Tous les parcours sont certifiants ?
                    'certifiante' => 1,
                    'contact-formation' => [
                        // Référent pédagogique
                        'type-contact' => 3,
                        'coordonnees' => [
                            'nom' => $parcours->getRespParcours()  ? $parcours->getRespParcours()->getNom() : 'Non renseigné.' ,
                            'prenom' => $parcours->getRespParcours()  ? $parcours->getRespParcours()->getPrenom() : 'Non renseigné.' ,
                            'courriel' => $parcours->getRespParcours()  ? $parcours->getRespParcours()->getEmail() : 'Non renseigné.' ,
                            ]
                        ],
                    // tous les parcours sont en groupe (non personnalisés) ?
                    'parcours-de-formation' => 1,
                    'code-niveau-entree' => $parcours->getFormation()->getNiveauEntree()->value,
                    'action' => [
                                                    // A CHANGER
                        'rythme-formation' => $rythmeFormation,
                        // Code FORMACODE
                        'code-public-vise' => '31057', // A CHANGER
                        'niveau-entree-obligatoire' => 1,
                        'modalites-alternance' => $this->cleanString($parcours->getModalitesAlternance() ?? 'Non renseigné.'),
                        'modalites-enseignement' => $parcours->getModalitesEnseignement()->value ?? 'Non renseigné.', // A CHANGER
                        'conditions-specifiques' => 'Aucune', // A CHANGER
                        'prise-en-charge-frais-possible' => 1, // A CHANGER - 1 oui | 0 non
                        'modalites-entrees-sorties' => 0, // Entrées sorties à dates fixes : 0 | entrées / sorties permanentes : 1
                        'session' => [
                            'periode' => [
                                'debut' => '00000000', //AAAAMMJJ - A CHANGER
                                'fin' => '00000000' // AAAAMMJJ - A CHANGER
                            ],
                            'adresse-inscription' => [
                                'adresse' => [
                                    'ligne' => 'XXXXXXX', // A CHANGER
                                    'codepostal' => '51100', // A CHANGER
                                    'ville' => 'REIMS', // A CHANGER
                                ]
                            ]
                        ]

                    ],
                    'organisme-formation-responsable' => [
                        'numero-activite' => 'XXXXXXXXXXX', // A CHANGER 
                        'SIRET-organisme-formation' => ['SIRET' => '19511296600799'], // A VERIFIER
                        'nom-organisme' => 'UNIVERSITE DE REIMS CHAMPAGNE-ARDENNE (URCA)',
                        'raison-sociale' => 'XXXXXXXXXXXX', // A CHANGER
                        'coordonnees-organisme' => [
                            'coordonnees' => [
                                // Coordonnées complètes de l'organisme responsable de l'offre
                                // COORDONNEES DU SECRETARIAT ?
                            ]
                        ],
                        'contact-organisme' => [
                            'coordonnees' => [
                                // Coordonnées d'une personne de l'organisme responsable de l'offre
                                // Quelles coordonnées ?
                            ]
                        ]
                    ]
                ]
            ]

        ], 'xml', $contextOptions);

        return $xml;
       
    }

    /**
     * Valide le XML selon le format LHEO
     * @param string $xml Chaîne contenant le XML
     * @return bool Vrai si le XML est valide, Faux sinon
     */
    public function validateLheoSchema(string $xml) : bool {
        
        libxml_use_internal_errors(true);
        $xmlValidator = new \DOMDocument('1.0', 'UTF-8');
        $xmlValidator->loadXML($xml);
        $isValid = $xmlValidator->schemaValidate(__DIR__ . '/../../lheo.xsd');
        
        return $isValid;
        
    }

    public function isValidLHEO(Parcours $parcours) : bool {
        return $this->validateLheoSchema($this->generateLheoXMLFromParcours($parcours));
    }

    /**
     * Retire les <div></div> et <!--block-->
     * et renvoie la chaîne épurée
     * @param ?string $stringToClean Chaîne de caractère à nettoyer
     * @return string Chaîne nettoyée
     */
    public function removeHTMLEntitiesFromString(?string $stringToClean) : string {
        if($stringToClean !== null && gettype($stringToClean) === 'string'){
            $cleanedString = preg_replace('/(\&nbsp;)+/', '', $stringToClean);
            $cleanedString = preg_replace('/(<([^>]+)>)/', '', $cleanedString);
            return $cleanedString;
        }
        else {
            return "";
        }
    }

    /**
     * Nettoie une chaîne de caractères
     * - Apostrophes ’ ---> '
     * - Caractères non imprimables
     * @param ?string $stringToClean Chaîne à nettoyer
     * @return string Chaîne épurée
     */
    public function cleanString(?string $stringToClean) : string {
        $cleanedString = preg_replace('/’/m', "'", $stringToClean);
        $cleanedString = preg_replace('/[\x00-\x1F\x7F]/m', '', $cleanedString);
        return $cleanedString;
    }
}