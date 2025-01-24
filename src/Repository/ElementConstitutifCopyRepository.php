<?php 

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use App\Entity\Ue;

class ElementConstitutifCopyRepository extends EntityRepository {

    public function __construct(EntityManagerInterface $em, string $className){
        parent::__construct($em, $em->getClassMetadata($className));
    }

    public function getByUe(?Ue $ue): array
    {
        return $this->createQueryBuilder('ec')
            ->leftJoin('ec.ficheMatiere', 'fm')
            ->leftJoin('ec.typeEc', 'te')
            ->addSelect('fm')
            ->addSelect('te')
            ->andWhere('ec.ue = :ue')
            ->setParameter('ue', $ue)
            ->orderBy('ec.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}