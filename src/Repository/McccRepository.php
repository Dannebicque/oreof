<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/McccRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 20/02/2023 15:58
 */

namespace App\Repository;

use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Mccc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mccc>
 *
 * @method Mccc|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mccc|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mccc[]    findAll()
 * @method Mccc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class McccRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mccc::class);
    }

    public function save(Mccc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Mccc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findFromAnneeUniversitaire(int $idCampagneCollecte) : array {
        $qb = $this->createQueryBuilder('mccc');

        // Fiches matières hors diplôme
        $subQueryFicheMatiereHorsDiplome = $this->createQueryBuilder('mcccFirst')
            ->select('mcccFirst.id')
            ->join('mcccFirst.ficheMatiere', 'mcccFirstFiche')
            ->join('mcccFirstFiche.campagneCollecte', 'mcccFirstCampagne')
            ->andWhere('mcccFirstFiche.horsDiplome = 1')
            ->andWhere('mcccFirstCampagne.id = :idCampagne');
        
        // Fiches matières reliées à un parcours
        $subQueryFicheParcours = $this->createQueryBuilder('mcccSecond')
            ->select('mcccSecond.id')
            ->join('mcccSecond.ficheMatiere', 'mcccSecondFiche')
            ->join('mcccSecondFiche.parcours', 'mcccSecondParcours')
            ->join('mcccSecondParcours.dpeParcours', 'mcccSecondDpe')
            ->join('mcccSecondDpe.campagneCollecte', 'mcccSecondCampagne')
            ->andWhere('mcccSecondCampagne.id = :idCampagne');

        // Fiches matières mutualisées
        $subQueryFicheMutualisee = $this->createQueryBuilder('mcccThird')
            ->select('mcccThird.id')
            ->join('mcccThird.ficheMatiere', 'mcccThirdFiche')
            ->join('mcccThirdFiche.ficheMatiereParcours', 'mcccThirdMutu')
            ->join('mcccThirdMutu.parcours', 'mcccThirdParcours')
            ->join('mcccThirdParcours.dpeParcours', 'mcccThirdDpe')
            ->join('mcccThirdDpe.campagneCollecte', 'mcccThirdCampagne')
            ->andWhere('mcccThirdCampagne.id = :idCampagne');

        // Élément constitutif reliés à un parcours
        $subQueryElementConstitutif = $this->createQueryBuilder('mcccFourth')
            ->select('mcccFourth.id')
            ->join('mcccFourth.ec', 'mcccFourthEc')
            ->join('mcccFourthEc.parcours', 'mcccFourthParcours')
            ->join('mcccFourthParcours.dpeParcours', 'mcccFourthDpe')
            ->join('mcccFourthDpe.campagneCollecte', 'mcccFourthCampagne')
            ->andWhere('mcccFourthCampagne.id = :idCampagne');

        return $qb 
            ->select('DISTINCT mccc.id')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->in('mccc.id', $subQueryFicheMatiereHorsDiplome->getDQL()),
                    $qb->expr()->in('mccc.id', $subQueryFicheParcours->getDQL()),
                    $qb->expr()->in('mccc.id', $subQueryFicheMutualisee->getDQL()),
                    $qb->expr()->in('mccc.id', $subQueryElementConstitutif->getDQL())
                )
            )
            ->setParameter(':idCampagne', $idCampagneCollecte)
            ->getQuery()
            ->getResult();
    }
}
