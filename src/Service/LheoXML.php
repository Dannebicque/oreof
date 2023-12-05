<?php

namespace App\Service;

use App\Classes\CalculStructureParcours;
use App\Entity\Parcours;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class LheoXML {
    /**
     * Génère du contenu XML de l'offre de formation, à partir d'un parcours.
     * Le format est généré selon les spécifications du LHEO
     * @param Parcours $parcours Parcours à transformer en XML
     * @return string $xml Contenu XML
     */
    public function generateLheoXMLFromParcours(Parcours $parcours, bool $with_extras = false) : string {
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
        $intituleFormation = 'Non renseigné.';
        if($typeDiplomeLibelle = $parcours->getFormation()?->getTypeDiplome()?->getLibelle()){
            $intituleFormation = $typeDiplomeLibelle . " " . $parcours->getLibelle();
        }

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

        // Contact de la formation
        $referentsPedagogiques = [];
        if($parcours->getRespParcours()){
            $referentPedagogique = [
                // Référent pédagogique
                'type-contact' => 3,
                'coordonnees' => [
                    'nom' => $parcours->getRespParcours()  ? $parcours->getRespParcours()->getNom() : 'Non renseigné.' ,
                    'prenom' => $parcours->getRespParcours()  ? $parcours->getRespParcours()->getPrenom() : 'Non renseigné.' ,
                    'courriel' => $parcours->getRespParcours()  ? $parcours->getRespParcours()->getEmail() : 'Non renseigné.' ,
                ]
            ];
            $referentsPedagogiques[] = $referentPedagogique;
        }
        if($parcours->getCoResponsable()){
            $coReferentPedagogique = [
                // Référent pédagogique
                'type-contact' => 3,
                'coordonnees' => [
                    'nom' => $parcours->getCoResponsable()  ? $parcours->getCoResponsable()->getNom() : 'Non renseigné.' ,
                    'prenom' => $parcours->getCoResponsable()  ? $parcours->getCoResponsable()->getPrenom() : 'Non renseigné.' ,
                    'courriel' => $parcours->getCoResponsable()  ? $parcours->getCoResponsable()->getEmail() : 'Non renseigné.' ,
                ]
            ];
            $referentsPedagogiques[] = $coReferentPedagogique;
        }

        // Niveau d'entree
        $niveauEntree = -1;
        if($niveau = $parcours->getFormation()?->getNiveauEntree()){
            $niveauEntree = $niveau->value;
        }
        
        // Adresses de la composante d'inscription       
        $composantesInscription = [];

        if($composante = $parcours->getComposanteInscription()){
            $adresse = [
                'denomination' => '',
                'ligne' => '', 
                'codepostal' => '',
                'ville' => '',
            ];
            // Adresse de l'accueil
            $adresseComp = $composante->getAdresse();
            $adresse['denomination'] = $composante->getLibelle();
            $adresse['ligne'] = $adresseComp->getAdresse1() . " " . ($adresseComp->getAdresse2() ?? '');
            $adresse['codepostal'] = $adresseComp->getCodePostal();
            $adresse['ville'] = $adresseComp->getVille();
            // Téléphone 
            $telephone = ['numtel' => $composante->getTelStandard() ?? $composante->getTelComplementaire() ?? 'Non renseigné'];
            // Résultat 
            $result = [
                'type-contact' => 4 ,
                'coordonnees' => [
                    'adresse' => $adresse,
                    'telfixe' => $telephone,
                    'courriel' => $composante->getMailContact(),
                    'web' => ['urlweb' => $composante->getUrlSite()]
                ]
            ];
    
            $composantesInscription[] = $result;
        }
            
        
        
        // Modalités de l'alternance
        $modalitesAlternance = "La formation n'est pas dispensée en alternance";
        if($parcours->getModalitesAlternance()){
            if(!empty($parcours->getModalitesAlternance())){
                $modalitesAlternance = $this->cleanString($parcours->getModalitesAlternance());
            }
        }

        // Durée de la formation (durée cycle)
        $dureeCycle = 0;
        if($parcours->getTypeDiplome()){
            if($parcours->getTypeDiplome()->getSemestreFin() !== null && $parcours->getTypeDiplome()->getSemestreDebut() !== null){
                $dureeCycle = (($parcours->getTypeDiplome()->getSemestreFin() - $parcours->getTypeDiplome()->getSemestreDebut()) + 1) / 2;
            }
        }

        // Calculs ECTS
        $ects = 0;
        if($with_extras){
            $dto = new CalculStructureParcours();
            $ects = $dto->calcul($parcours)->heuresEctsFormation->sommeFormationEcts;
        }


        // code RNCP
        $rncp = 'RNCP00000';
        if($parcours->getFormation()?->getCodeRNCP()){
           $rncp = 'RNCP' . $parcours->getFormation()->getCodeRNCP();
        }

        // Contact Organisme (composante)
        $coordonneesComposante = [

        ];


        // Génération du XML
        $encoder = new XmlEncoder([
        ]);
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
                    'objectif-formation' => $this->cleanString(($parcours->getObjectifsParcours() ?? 'Non renseigné.')),
                    'resultats-attendus' => $this->cleanString(($parcours->getResultatsAttendus() ?? 'Non renseigné.')),
                    'contenu-formation' => $this->cleanString(($parcours->getContenuFormation() ?? 'Non renseigné.')),
                    'certifiante' => 1,
                    'contact-formation' => $referentsPedagogiques,
                    'parcours-de-formation' => 1,
                    'code-niveau-entree' => $niveauEntree,
                    'certification' => [
                        'code-RNCP' => $rncp
                    ],
                    'code-niveau-sortie' => $parcours->getFormation()?->getNiveauSortie()->value ?? 0, // A CHANGER
                    'action' => [
                                                    
                        'rythme-formation' => $rythmeFormation,
                        'code-public-vise' => '00000',
                        'niveau-entree-obligatoire' => 1,
                        'modalites-alternance' => $modalitesAlternance,
                        'modalites-enseignement' => $parcours->getModalitesEnseignement() ? $parcours->getModalitesEnseignement()->value : 1,
                        'conditions-specifiques' => $parcours->getPrerequis() ?? 'Aucune condition spécifique.',
                        'prise-en-charge-frais-possible' => 1, // A CHANGER - 1 oui | 0 non 
                        'modalites-entrees-sorties' => 0,
                        'duree-cycle' => $dureeCycle,
                        'session' => [
                            'periode' => [
                                'debut' => '00000000',
                                'fin' => '00000000' 
                            ],
                            'adresse-inscription' => [
                                'adresse' => [
                                    'ligne' => '-',
                                    'codepostal' => '00000',
                                    'ville' => '-' 
                                ]
                            ]
                        ],
                        'restauration' => $parcours->getVille()?->getEtablissement()?->getEtablissementInformation()?->getRestauration() ?? "Non renseigné.",
                        'hebergement' => $parcours->getVille()?->getEtablissement()?->getEtablissementInformation()?->getHebergement() ?? "Non renseigné.",
                        'transport' => $parcours->getVille()?->getEtablissement()?->getEtablissementInformation()?->getTransport() ?? "Non renseigné" 

                    ],
                    'organisme-formation-responsable' => [
                        'numero-activite' => '2151P001151',
                        'SIRET-organisme-formation' => ['SIRET' => '19511296600799'],
                        'nom-organisme' => 'Université de Reims Champagne-Ardenne',
                        'raison-sociale' => 'Université de Reims Champagne-Ardenne',
                        'coordonnees-organisme' => [
                            // Coordonnées de l'URCA
                            'coordonnees' => [
                                'adresse' => [
                                    'denomination' => 'Université de Reims Champagne-Ardenne',
                                    'ligne' => '2 Avenue Robert Schuman',
                                    'codepostal' => '51724',
                                    'ville' => 'REIMS CEDEX'
                                ]              
                            ]
                        ],
                        'contact-organisme' => $composantesInscription
                    ],
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