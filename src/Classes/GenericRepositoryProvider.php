<?php

namespace App\Classes;

use App\Repository\GenericRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GenericRepositoryProvider {

    public const CURRENT_YEAR_DATABASE = "default";
    public const NEXT_YEAR_DATABASE = "next_year";

    private EntityManagerInterface $entityManager;
    private ManagerRegistry $doctrine;
    private GenericRepository $genericRepository;

    public function __construct(ManagerRegistry $doctrine){
        $this->doctrine = $doctrine;
    }

    public function setConfiguration(string $databaseVersion){
        if(in_array($databaseVersion, [self::CURRENT_YEAR_DATABASE, self::NEXT_YEAR_DATABASE], true) === false){
            throw new \Exception("Database version not recognized. Should be 'current' or 'next_year'.");
        }

        $this->entityManager = $this->doctrine->getManager($databaseVersion);

        return $this;
    }

    public function setCurrentRepository(
        string $className
    ){
        $this->genericRepository = (new GenericRepository())->setConfiguration($className, $this->entityManager);
        return $this->genericRepository;
    }

    public function getEntityManager() {
        return $this->entityManager;
    }

    public function getCurrentRepository() {
        return $this->genericRepository;
    }

    public function getCurrentDatabaseName() {
        return $this->entityManager->getConnection()->getDatabase();
    }
}