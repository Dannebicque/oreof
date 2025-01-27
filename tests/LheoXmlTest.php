<?php

namespace App\Tests;

use App\Classes\GenericRepositoryProvider;
use App\Entity\Parcours;
use App\Entity\User;
use App\Service\LheoXML;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LheoXmlTest extends WebTestCase
{

    private static GenericRepositoryProvider $currentGenericProvider;
    private static GenericRepositoryProvider $nextGenericProvider;

    private static LheoXML $lheoXML;

    public static function setUpBeforeClass(): void
    {
        $container = static::getContainer();
    
        self::$currentGenericProvider = $container
            ->get('current.generic.repository.provider')
            ->setConfiguration(GenericRepositoryProvider::CURRENT_YEAR_DATABASE);

        self::$nextGenericProvider = $container
            ->get('next.generic.repository.provider')
            ->setConfiguration(GenericRepositoryProvider::NEXT_YEAR_DATABASE);
    
        self::$lheoXML = $container->get(LheoXML::class);
    }

    /**
     * @dataProvider lheoXmlProvider
     */
    public function testLheoXmlLoadsCorrectly(
        int $idParcours, 
        string $libelleParcours,
        string $modifCurrent,
        string $modifNext,
        string $methodName,
        string $lheoXmlAttributeName
    ){
        $parcoursXmlCurrent = self::$currentGenericProvider
            ->setCurrentRepository(Parcours::class)
            ->findOneById($idParcours);
        $parcoursXmlNext = self::$nextGenericProvider 
            ->setCurrentRepository(Parcours::class)
            ->findOneById($idParcours);

        $this->assertEquals($libelleParcours, $parcoursXmlCurrent->getLibelle());
        $this->assertEquals($libelleParcours, $parcoursXmlNext->getLibelle());

        $currentXML = self::$lheoXML->generateLheoXMLFromParcours($parcoursXmlCurrent);
        $nextXML = self::$lheoXML->generateLheoXMLFromParcours($parcoursXmlNext);

        // Le LHEO est valide au départ
        $this->assertTrue(self::$lheoXML->validateLheoSchema($currentXML));
        $this->assertTrue(self::$lheoXML->validateLheoSchema($nextXML));

        // Modification
        $parcoursXmlCurrent->{$methodName}($modifCurrent);
        $parcoursXmlNext->{$methodName}($modifNext);

        self::$currentGenericProvider->getEntityManager()->persist($parcoursXmlCurrent);
        self::$currentGenericProvider->getEntityManager()->flush();

        self::$nextGenericProvider->getEntityManager()->persist($parcoursXmlNext);
        self::$nextGenericProvider->getEntityManager()->flush();

        // Récupération des nouvelles données
        $parcoursXmlCurrent = null;
        $parcoursXmlNext = null;
        $parcoursXmlCurrent = self::$currentGenericProvider->getCurrentRepository()->findOneById($idParcours);
        $parcoursXmlNext = self::$nextGenericProvider->getCurrentRepository()->findOneById($idParcours);

        $newCurrentXml = self::$lheoXML->generateLheoXMLFromParcours($parcoursXmlCurrent);
        $newNextXml = self::$lheoXML->generateLheoXMLFromParcours($parcoursXmlNext);

        // Vérifications
        $this->assertTrue(self::$lheoXML->validateLheoSchema($newCurrentXml));
        $this->assertTrue(self::$lheoXML->validateLheoSchema($newNextXml));

        $newCurrentXmlArray = json_decode(json_encode(simplexml_load_string($newCurrentXml)), true);
        $newNextXmlArray = json_decode(json_encode(simplexml_load_string($newNextXml)), true);

        $this->assertEquals(
            $modifCurrent,
            $newCurrentXmlArray['offres']['formation'][$lheoXmlAttributeName]
        );
        $this->assertEquals(
            $modifNext,
            $newNextXmlArray['offres']['formation'][$lheoXmlAttributeName]
        );
        $this->assertNotEquals(
            $newCurrentXmlArray['offres']['formation'][$lheoXmlAttributeName],
            $newNextXmlArray['offres']['formation'][$lheoXmlAttributeName]
        );
    }

    /**
     * @dataProvider newResponsableParcoursProvider
     */
    public function testChangeUser(
        int $idParcours,
        int $idUser,
        string $nom,
        string $prenom
    ){
        // Variables de départ
        $parcoursCurrent = self::$currentGenericProvider
            ->getCurrentRepository()
            ->findOneById($idParcours);

        $parcoursNext = self::$nextGenericProvider
            ->getCurrentRepository()
            ->findOneById($idParcours);

        $nomPrenomInitial = "{$parcoursCurrent->getRespParcours()->getNom()} {$parcoursCurrent->getRespParcours()->getPrenom()}";

          
        // Les deux responsables sont identiques au départ
        $this->assertEquals(
            $parcoursCurrent->getRespParcours()->getId(),
            $parcoursNext->getRespParcours()->getId()
        );

        // Génération du XML initial
        $xmlCurrentInitial = self::$lheoXML->generateLheoXMLFromParcours($parcoursCurrent);
        $xmlNextInitial = self::$lheoXML->generateLheoXMLFromParcours($parcoursNext);

        $currentXmlArray = json_decode(json_encode(simplexml_load_string($xmlCurrentInitial)), true);
        $nextXmlArray = json_decode(json_encode(simplexml_load_string($xmlNextInitial)), true);

        $this->assertTrue(self::$lheoXML->validateLheoSchema($xmlCurrentInitial));
        $this->assertTrue(self::$lheoXML->validateLheoSchema($xmlNextInitial));

        // Comparaison du responsable de parcours
        // avec gestion du cas où il y a plusieurs responsables
        // la valeur comparée est la première
        $currentXmlUser = $currentXmlArray['offres']['formation']['contact-formation'];
        $currentXmlUser = array_key_exists(0, $currentXmlUser)
            ? $currentXmlUser[0]['coordonnees']
            : $currentXmlUser['coordonnees'];
        
        $nextXmlUser = $nextXmlArray['offres']['formation']['contact-formation'];
        $nextXmlUser = array_key_exists(0, $nextXmlUser)
            ? $nextXmlUser[0]['coordonnees']
            : $nextXmlUser['coordonnees'];
        
        $this->assertEquals($currentXmlUser['nom'], $nextXmlUser['nom']);
        $this->assertEquals($currentXmlUser['prenom'], $nextXmlUser['prenom']);

        // Modification avec nouveau responsable sur la base 'next'
        $newNextUser = self::$nextGenericProvider
            ->setCurrentRepository(User::class)
            ->findOneById($idUser);

        $parcoursNext->setRespParcours($newNextUser);
        self::$nextGenericProvider->getEntityManager()->persist($parcoursNext);
        self::$nextGenericProvider->getEntityManager()->flush();

        // Récupération des nouvelles données
        $newParcoursCurrent = self::$currentGenericProvider
            ->setCurrentRepository(Parcours::class)
            ->findOneById($idParcours);
        $newParcoursNext = self::$nextGenericProvider
            ->setCurrentRepository(Parcours::class)
            ->findOneById($idParcours);

        $newXmlCurrent = self::$lheoXML->generateLheoXMLFromParcours($newParcoursCurrent);
        $newXmlNext = self::$lheoXML->generateLheoXMLFromParcours($newParcoursNext);

        $newCurrentXmlArray = json_decode(json_encode(simplexml_load_string($newXmlCurrent)), true);
        $newNextXmlArray = json_decode(json_encode(simplexml_load_string($newXmlNext)), true);

        // Vérifications
        $newCurrentXmlUser = $newCurrentXmlArray['offres']['formation']['contact-formation'];
        $newCurrentXmlUser = array_key_exists(0, $newCurrentXmlUser)
            ? $newCurrentXmlUser[0]['coordonnees']
            : $newCurrentXmlUser['coordonnees'];
        $newNextXmlUser = $newNextXmlArray['offres']['formation']['contact-formation'];
        $newNextXmlUser = array_key_exists(0, $newNextXmlUser)
            ? $newNextXmlUser[0]['coordonnees']
            : $newNextXmlUser['coordonnees'];

        // Le nom initial reste inchangé
        $this->assertEquals(
            $nomPrenomInitial, 
            "{$newCurrentXmlUser['nom']} {$newCurrentXmlUser['prenom']}"
        );

        // Le changement a bien eu lieu : nouveau != ancien
        $this->assertNotEquals($newCurrentXmlUser['nom'], $newNextXmlUser['nom']);
        $this->assertNotEquals($newCurrentXmlUser['prenom'], $newNextXmlUser['prenom']);

        // Le nouveau est bien celui saisi
        $this->assertEquals($newNextXmlUser['nom'], $nom);
        $this->assertEquals($newNextXmlUser['prenom'], $prenom);

        // Le LHEO est valide après changement
        $this->assertTrue(self::$lheoXML->validateLheoSchema($newXmlCurrent));
        $this->assertTrue(self::$lheoXML->validateLheoSchema($newXmlNext));
    }

    public function lheoXmlProvider(){
        return [
            [
                79, 
                "Médicaments, Qualité, Réglementation", 
                "CONTENU CURRENT",
                "CONTENU NEXT #12345", 
                "setContenuFormation",
                "contenu-formation",
            ],
            [
                512,
                "Finance comptabilité contrôle  - Troyes",
                "RESULTATS CURRENT #456",
                "RESULTATS NEXT #987",
                "setResultatsAttendus",
                "resultats-attendus",
            ],
            [
                244,
                "Gestion de l’environnement : eau, sol, roche",
                "OBJECTIFS CURRENT #1",
                "OBJECTIFS NEXT #2",
                "setObjectifsParcours",
                "objectif-formation",
            ],
        ];
    }

    public function newResponsableParcoursProvider(){
        return [
            [511, 7, 'LANG-LANNOY', 'Emeline'],
            [188, 8, 'ANNEBICQUE', 'David'],
            [102, 742, 'MARCHAL', 'Pol'],
        ];
    }
}
