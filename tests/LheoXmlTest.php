<?php

namespace App\Tests;

use App\Classes\GenericRepositoryProvider;
use App\Entity\Parcours;
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
}
