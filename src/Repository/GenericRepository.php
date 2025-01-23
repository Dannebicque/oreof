<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public:true)]
class GenericRepository extends EntityRepository {

    private string $className;
    private string $databaseName;

    public function __construct(){}

    /**
     * @param string $className Nom de l'entité que l'on souhaite utiliser
     * @param EntityManagerInterface EntityManager à utiliser
     * @return GenericRepository Retourne l'objet courant
     */
    public function setConfiguration(string $className, EntityManagerInterface $entityManager) : static {
        $this->className = $className;
        $this->databaseName = $entityManager->getConnection()->getDatabase();
        parent::__construct($entityManager, $entityManager->getClassMetadata($className));

        return $this;
    }

    /**
     * @return string Nom de la base de données active
     */
    public function getDatabaseName() : string {
        return $this->databaseName;
    }

    /**
     * @return string Nom de l'entité actuellement utilisée
     */
    public function getClassName() {
        return $this->className;
    }
}