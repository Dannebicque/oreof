<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file //wsl.localhost/Ubuntu/home/louca/oreof-stack/oreofv2/src/Repository/HelpImageRepository.php
 * @author louca
 * @project oreofv2
 * @lastUpdate 04/05/2026 14:19
 */

namespace App\Repository;

use App\Entity\HelpImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HelpImage>
 *
 * @method HelpImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method HelpImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method HelpImage[]    findAll()
 * @method HelpImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HelpImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HelpImage::class);
    }

    public function search(?string $query = null): array
    {
        $qb = $this->createQueryBuilder('h')
            ->orderBy('h.dateCreation', 'DESC');

        if ($query) {
            $qb->andWhere('h.nom LIKE :query OR h.fichier LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        return $qb->getQuery()->getResult();
    }
}

