<?php

namespace App\Tests;

use App\Entity\Parcours;
use App\Repository\GenericRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParallelDatabaseTest extends KernelTestCase
{
    private static ?GenericRepository $currentVersionRepository;

    private static ?GenericRepository $nextVersionRepository;

    public static function setUpBeforeClass(): void {
        $container = static::getContainer();
    
        self::$currentVersionRepository = $container->get('current.version.repository');
        self::$nextVersionRepository = $container->get('next.version.repository');

        self::$currentVersionRepository->setConfiguration(Parcours::class, 'current');
        self::$nextVersionRepository->setConfiguration(Parcours::class, 'next');
    }

    public static function tearDownAfterClass(): void {
        self::$currentVersionRepository = null;
        self::$nextVersionRepository = null;
    }

    public function testBothConnectionsHaveSeparateDatabases(): void {
        $kernel = self::bootKernel();

        // Il y a bien deux bases de données différentes
        $this->assertEquals('versioning_current_test', self::$currentVersionRepository->getDatabaseName());
        $this->assertEquals('versioning_next_year_test', self::$nextVersionRepository->getDatabaseName());
        $this->assertNotEquals(
            self::$currentVersionRepository->getDatabaseName(),
            self::$nextVersionRepository->getDatabaseName()
        );
    }

    public function testOneChangeDoesNotAffectTheOther(){
        $currentParcours = self::$currentVersionRepository->findOneById(405);
        $nextParcours = self::$nextVersionRepository->findOneById(405);

        $txtCurrent = "CONTENU FORMATION CURRENT #AAA";
        $txtNext = "CONTENU FORMATION NEXT #BBB";
        $txtCurrentSecond = "CONTENU FORMATION CURRENT #CCC";

        // C'est le bon parcours qui est récupéré
        $this->assertEquals($currentParcours->getId(), 405);
        $this->assertEquals($nextParcours->getId(), 405);
        $this->assertEquals($currentParcours->getId(), $nextParcours->getId());

        // On modifie l'actuel, mais la version à venir ne doit pas changer
        $currentParcours->setContenuFormation($txtCurrent);
        $this->assertEquals($txtCurrent, $currentParcours->getContenuFormation());
        $this->assertNotEquals($txtCurrent, $nextParcours->getContenuFormation());

        // On modifie la version à venir, mais l'actuel ne doit pas changer
        $nextParcours->setContenuFormation($txtNext);
        $this->assertEquals($txtCurrent, $currentParcours->getContenuFormation());
        $this->assertEquals($txtNext, $nextParcours->getContenuFormation());
        $this->assertNotEquals($nextParcours->getContenuFormation(), $currentParcours->getContenuFormation());

        // On modifie à nouveau l'actuel, mais la version à venir ne doit pas changer
        $currentParcours->setContenuFormation($txtCurrentSecond);
        $this->assertEquals($txtNext, $nextParcours->getContenuFormation());
        $this->assertEquals($txtCurrentSecond, $currentParcours->getContenuFormation());

        // On persiste les changements dans la base de données
        $currentEntityManager = self::$currentVersionRepository->getGenericEntityManager();
        $currentEntityManager->persist($currentParcours);
        $currentEntityManager->flush();

        $nextEntityManager = self::$nextVersionRepository->getGenericEntityManager();
        $nextEntityManager->persist($nextParcours);
        $nextEntityManager->flush();

        // On libère les parcours, pour les récupérer par la suite
        // et vérifier que les données ont bien été sauvegardées
        $currentParcours = null;
        $nextParcours = null;

        $currentVersionPersistedParcours = self::$currentVersionRepository->findOneById(405);
        $nextVersionPersistedParcours = self::$nextVersionRepository->findOneById(405);

        $this->assertEquals($txtCurrentSecond, $currentVersionPersistedParcours->getContenuFormation());
        $this->assertEquals($txtNext, $nextVersionPersistedParcours->getContenuFormation());
        $this->assertNotEquals(
            $currentVersionPersistedParcours->getContenuFormation(),
            $nextVersionPersistedParcours->getContenuFormation()
        );
    }
}
