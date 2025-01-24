<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

class FicheMatiereCopyRepository extends EntityRepository {

    public function __construct(EntityManagerInterface $em, string $className){
        parent::__construct($em, $em->getClassMetadata($className));
    }
}