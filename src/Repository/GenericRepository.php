<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public:true)]
class GenericRepository extends EntityRepository {

    public const CURRENT_YEAR_DATABASE = "current" ;
    public const NEXT_YEAR_DATABASE = "next";

    private string $className;

    private EntityManagerInterface $entityManager;

    public function __construct(private ManagerRegistry $doctrine){}

    /**
     * Configure le Repository générique, en choisissant la bonne entité, et la bonne connexion
     * @param string $className Nom de l'entité que l'on souhaite utiliser (ex: Parcours::class)
     * @param string $databaseVersion Version de la base de données à utiliser ('current' ou 'next')
     * @return GenericRepository On peut utiliser le retour directement avec les méthodes comme findBy()
     */
    public function setConfiguration(string $className, string $databaseVersion) : static {
        if(in_array($databaseVersion, ["current", "next"], true) === false){
            throw new \Exception("Database version not recognized. Should be 'current' or 'next'.");
        }

        $this->className = $className;
        $this->entityManager = $databaseVersion === "current" 
            ? $this->doctrine->getManager('default') 
            : $this->doctrine->getManager('next_year');

        parent::__construct(
            $this->entityManager,
            $this->entityManager->getClassMetadata($this->className)
        );

        return $this;
    }

    /**
     * @return string Nom de la base de données active
     */
    public function getDatabaseName() : string {
        return $this->entityManager->getConnection()->getDatabase();
    }

    public function getGenericEntityManager(){
        return $this->entityManager;
    }
}