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

    public function testLheoXmlLoadsCorrectly(){
        $idParcours = 147;

        $parcoursXmlCurrent = self::$currentGenericProvider
            ->setCurrentRepository(Parcours::class)
            ->findOneById($idParcours);
        $parcoursXmlNext = self::$nextGenericProvider 
            ->setCurrentRepository(Parcours::class)
            ->findOneById($idParcours);

        $libelleParcours = "LLCER Espagnol (Reims)";
        $this->assertEquals($libelleParcours, $parcoursXmlCurrent->getLibelle());
        $this->assertEquals($libelleParcours, $parcoursXmlNext->getLibelle());

        $currentXML = self::$lheoXML->generateLheoXMLFromParcours($parcoursXmlCurrent);
        $nextXML = self::$lheoXML->generateLheoXMLFromParcours($parcoursXmlNext);

        // Le LHEO est valide au départ
        $this->assertTrue(self::$lheoXML->validateLheoSchema($currentXML));
        $this->assertTrue(self::$lheoXML->validateLheoSchema($nextXML));

        // Modification
        $modificationResultatsCurrent = "RESULTATS CURRENT #123";
        $modificationResultatsNext = "RESULTATS NEXT #456";
        $parcoursXmlCurrent->setResultatsAttendus($modificationResultatsCurrent);
        $parcoursXmlNext->setResultatsAttendus($modificationResultatsNext);

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
            $modificationResultatsCurrent,
            $newCurrentXmlArray['offres']['formation']['resultats-attendus']
        );
        $this->assertEquals(
            $modificationResultatsNext,
            $newNextXmlArray['offres']['formation']['resultats-attendus']
        );
    }
}
