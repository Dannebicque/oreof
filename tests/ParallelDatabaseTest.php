<?php

namespace App\Tests;

use App\Classes\GenericRepositoryProvider;
use App\Entity\Parcours;
use App\Entity\User;
use App\Repository\GenericRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParallelDatabaseTest extends KernelTestCase
{
    private static ?GenericRepositoryProvider $currentGenericProvider;
    private static ?GenericRepositoryProvider $nextGenericProvider;

    public static function setUpBeforeClass(): void {
        $container = static::getContainer();
    
        self::$currentGenericProvider = $container
            ->get('current.generic.repository.provider')
            ->setConfiguration(GenericRepositoryProvider::CURRENT_YEAR_DATABASE);

        self::$nextGenericProvider = $container
            ->get('next.generic.repository.provider')
            ->setConfiguration(GenericRepositoryProvider::NEXT_YEAR_DATABASE);
    
    }

    public static function tearDownAfterClass(): void {
        self::$currentGenericProvider = null;
        self::$nextGenericProvider = null;
    }

    public function testBothConnectionsHaveSeparateDatabases(): void {
        $kernel = self::bootKernel();

        // Il y a bien deux bases de données différentes
        $this->assertEquals('versioning_current_test', self::$currentGenericProvider->getCurrentDatabaseName());
        $this->assertEquals('versioning_next_year_test', self::$nextGenericProvider->getCurrentDatabaseName());
        $this->assertNotEquals(
            self::$currentGenericProvider->getCurrentDatabaseName(),
            self::$nextGenericProvider->getCurrentDatabaseName()
        );
    }

    public function testOneChangeDoesNotAffectTheOther(){
        $currentParcours = self::$currentGenericProvider
            ->setCurrentRepository(Parcours::class)
            ->findOneById(405);
        $nextParcours = self::$nextGenericProvider
            ->setCurrentRepository(Parcours::class)
            ->findOneById(405);

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
        self::$currentGenericProvider->getEntityManager()->persist($currentParcours);
        self::$currentGenericProvider->getEntityManager()->flush();

        self::$nextGenericProvider->getEntityManager()->persist($nextParcours);
        self::$nextGenericProvider->getEntityManager()->flush();

        // On libère les parcours, pour les récupérer par la suite
        // et vérifier que les données ont bien été sauvegardées
        $currentParcours = null;
        $nextParcours = null;

        $currentVersionPersistedParcours = self::$currentGenericProvider->getCurrentRepository()->findOneById(405);
        $nextVersionPersistedParcours = self::$nextGenericProvider->getCurrentRepository()->findOneById(405);

        $this->assertEquals($txtCurrentSecond, $currentVersionPersistedParcours->getContenuFormation());
        $this->assertEquals($txtNext, $nextVersionPersistedParcours->getContenuFormation());
        $this->assertNotEquals(
            $currentVersionPersistedParcours->getContenuFormation(),
            $nextVersionPersistedParcours->getContenuFormation()
        );
    }

    public function testChangingNestedAttributes(){
        // Parcours
        $currentParcours = self::$currentGenericProvider->getCurrentRepository()->findOneById(85);
        $nextParcours = self::$nextGenericProvider->getCurrentRepository()->findOneById(85);

        // Utilisateur
        $currentUser = self::$currentGenericProvider->setCurrentRepository(User::class)->findOneById(7);
        $nextUser = self::$nextGenericProvider->setCurrentRepository(User::class)->findOneById(7);

        // Réinitialisation des valeurs pour les tests
        $currentParcours->setRespParcours($currentUser);
        $nextParcours->setRespParcours($nextUser);
        
        $this->assertEquals(7, $currentParcours->getRespParcours()->getId());
        $this->assertEquals(7, $nextParcours->getRespParcours()->getId());
        
        // On change la base 'next', et on vérifie qu'il n'y a que celle-là qui a changé
        $changeUserNext = self::$nextGenericProvider->getCurrentRepository()->findOneById(8);
        $nextParcours->setRespParcours($changeUserNext);
        $this->assertEquals(7, $currentParcours->getRespParcours()->getId());
        $this->assertEquals(8, $nextParcours->getRespParcours()->getId());

        self::$currentGenericProvider->getEntityManager()->persist($currentParcours);
        self::$currentGenericProvider->getEntityManager()->flush();

        self::$nextGenericProvider->getEntityManager()->persist($nextParcours);
        self::$nextGenericProvider->getEntityManager()->flush();
        
        // Puis on récupère à nouveau les données pour voir si elles ont changé correctement
        $currentParcours = null;
        $nextParcours = null;

        $currentParcours = self::$currentGenericProvider->setCurrentRepository(Parcours::class)->findOneById(85);
        $nextParcours = self::$nextGenericProvider->setCurrentRepository(Parcours::class)->findOneById(85);

        $this->assertEquals(
            "LANG-LANNOY Emeline 7",
            $currentParcours->getRespParcours()->getNom() 
            . " " 
            . $currentParcours->getRespParcours()->getPrenom()
            . " {$currentParcours->getRespParcours()->getId()}"
        );
        $this->assertEquals(
            "ANNEBICQUE David 8", 
            $nextParcours->getRespParcours()->getNom()
            . " "
            . $nextParcours->getRespParcours()->getPrenom()
            . " {$nextParcours->getRespParcours()->getId()}"
        );
    }
}
