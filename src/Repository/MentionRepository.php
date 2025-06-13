<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/MentionRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/02/2023 23:24
 */

namespace App\Repository;

use App\Entity\Domaine;
use App\Entity\Mention;
use App\Entity\TypeDiplome;
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

    public function findByDomaineAndTypeDiplome(Domaine $domaine, TypeDiplome $typeDiplome): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.domaine = :domaine')
            ->andWhere('m.typeDiplome = :typeDiplome')
            ->setParameter('domaine', $domaine)
            ->setParameter('typeDiplome', $typeDiplome)
            ->orderBy('m.libelle', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findByDomaineAndTypeDiplomeArray(Domaine $domaine, TypeDiplome $typeDiplome): array
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

    public function findBySearch(string|null $q, string|null $sort, string|null $direction, ?int $limit = null, ?int $offset = null): array
    {
        $query = $this->createQueryBuilder('m');

        if ($q !== null && $q !== '') {
            $query->andWhere('m.libelle LIKE :q')
                ->orWhere('m.sigle LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        if ($sort !== null && $sort !== '') {
            if ($sort === 'type_diplome') {
                $query->leftJoin('m.typeDiplome', 't')
                    ->orderBy('t.libelle', $direction);
            } elseif ($sort === 'domaine') {
                $query->leftJoin('m.domaine', 'd')
                    ->orderBy('d.libelle', $direction);
            } else {
                $query->orderBy('m.' . $sort, $direction);
            }
        }

        if ($limit !== null) {
            $query->setMaxResults($limit);
        }

        if ($offset !== null) {
            $query->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Compte le nombre total de mentions correspondant aux critères de recherche.
     *
     * @param string|null $q Terme de recherche
     * @return int Nombre total de mentions
     */
    public function countBySearch(string|null $q): int
    {
        $query = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)');

        if ($q !== null && $q !== '') {
            $query->andWhere('m.libelle LIKE :q')
                ->orWhere('m.sigle LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        return (int)$query->getQuery()->getSingleScalarResult();
    }
}
