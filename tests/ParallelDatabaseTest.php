<?php

namespace App\Tests;

use App\Entity\Parcours;
use App\Repository\GenericRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParallelDatabaseTest extends KernelTestCase
{
    public function testBothConnectionsHaveSeparateDatabases(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();

        $currentVersionRepository = $container->get('current.version.repository');
        $nextVersionRepository = $container->get('next.version.repository');

        $currentVersionRepository->setConfiguration(Parcours::class, 'current');
        $nextVersionRepository->setConfiguration(Parcours::class, 'next');

        // Il y a bien deux bases de données différentes
        $this->assertEquals('versioning_current_test', $currentVersionRepository->getDatabaseName());
        $this->assertEquals('versioning_next_year_test', $nextVersionRepository->getDatabaseName());
        $this->assertNotEquals(
            $currentVersionRepository->getDatabaseName(),
            $nextVersionRepository->getDatabaseName()
        );
    }
}
