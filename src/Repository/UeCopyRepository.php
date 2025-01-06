<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Semestre;

class UeCopyRepository extends EntityRepository {
    public function __construct(EntityManagerInterface $em, string $className){
        parent::__construct($em, $em->getClassMetadata($className));
    }

    public function getBySemestre(Semestre $semestre): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.ueRaccrochee', 'r')
            ->addSelect('r')
            ->andWhere('u.semestre = :semestre')
            ->setParameter('semestre', $semestre)
            ->getQuery()
            ->getResult();
    }
}