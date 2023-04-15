<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/ElementConstitutifRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 11:26
 */

namespace App\Repository;

use App\Entity\AnneeUniversitaire;
use App\Entity\Composante;
use App\Entity\EcUe;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\Ue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<FicheMatiere>
 *
 * @method FicheMatiere|null find($id, $lockMode = null, $lockVersion = null)
 * @method FicheMatiere|null findOneBy(array $criteria, array $orderBy = null)
 * @method FicheMatiere[]    findAll()
 * @method FicheMatiere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FicheMatiereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FicheMatiere::class);
    }

    public function save(FicheMatiere $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FicheMatiere $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByParcours(Parcours $parcours): array
    {
        //soit la fiche est portée par le parcours, soit elle est mutualisée avec ce parcours
        return $this->createQueryBuilder('f')
            ->leftJoin('f.parcours', 'p')
            ->leftJoin('f.ficheMatiereParcours', 'pm')
            ->where('p = :parcours')
            ->orWhere('pm = :parcours')
            ->setParameter('parcours', $parcours)
            ->orderBy('f.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
