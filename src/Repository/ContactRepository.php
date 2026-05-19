<?php

namespace App\Repository;

use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contact>
 *
 * @method Contact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    public function findFromAnneeUniversitaire(int $idCampagneCollecte, array $exclusionEtatDpe) : array {
        $qb = $this->createQueryBuilder('c');
        return $qb
            ->select('c.id')
            ->join('c.parcours', 'p')
            ->join('p.dpeParcours', 'dpe')
            ->join('dpe.campagneCollecte', 'campagne')
            ->andWhere('campagne.id = :idCampagne')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->orX(
                        $qb->expr()->isNull('p.isSoftDeleted'),
                        $qb->expr()->eq('p.isSoftDeleted', 0)
                    ),
                    $qb->expr()->notIn('dpe.etatReconduction', ':exclusionEtatDpe')
                )
            )
            ->setParameter(':idCampagne', $idCampagneCollecte)
            ->setParameter(':exclusionEtatDpe', $exclusionEtatDpe)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Contact[] Returns an array of Contact objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Contact
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
