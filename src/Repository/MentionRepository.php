<?php

namespace App\Repository;

use App\Entity\Domaine;
use App\Entity\Mention;
use App\TypeDiplome\Source\TypeDiplomeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mention>
 *
 * @method Mention|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mention|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mention[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MentionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mention::class);
    }

    public function findAll(): array
    {
        return $this->findBy([], ['libelle' => 'ASC']);
    }

    public function save(Mention $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Mention $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByDomaineAndTypeDiplome(Domaine $domaine, TypeDiplomeInterface $typeDiplome): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.domaine = :domaine')
            ->andWhere('m.typeDiplome = :typeDiplome')
            ->setParameter('domaine', $domaine)
            ->setParameter('typeDiplome', $typeDiplome::class)
            ->orderBy('m.libelle', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findByDomaineAndTypeDiplomeArray(Domaine $domaine, mixed $typeDiplome): array
    {
        $data = $this->findByDomaineAndTypeDiplome($domaine, $typeDiplome);

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->getId(),
                'libelle' => $item->getLibelle()];
        }

        return $result;
    }
}
