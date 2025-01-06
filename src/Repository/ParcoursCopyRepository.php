<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ParcoursCopyRepository extends EntityRepository {

    public function __construct(EntityManagerInterface $em, string $className){
        parent::__construct($em, $em->getClassMetadata($className));
    }
}