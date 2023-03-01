<?php

namespace App\Repository;

use App\Entity\AnneeUniversitaire;
use App\Entity\Composante;
use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Formation>
 *
 * @method Formation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Formation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Formation[]    findAll()
 * @method Formation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

    public function save(Formation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Formation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByComposanteDpe(UserInterface $user, AnneeUniversitaire $anneeUniversitaire, array $sorts = []): array
    {
        $query = $this->createQueryBuilder('f')
            ->innerJoin(Composante::class, 'c', 'WITH', 'f.composantePorteuse = c.id')
            ->where('c.responsableDpe = :user')
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('user', $user)
            ->setParameter('anneeUniversitaire', $anneeUniversitaire);

        foreach ($sorts as $sort => $direction) {
            $query->addOrderBy('f.' . $sort, $direction);
        }

        return $query->getQuery()
            ->getResult();
    }
}
