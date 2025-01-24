<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/ParcoursRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 02/02/2023 18:32
 */

namespace App\Repository;

use App\Entity\CampagneCollecte;
use App\Entity\Composante;
use App\Entity\Formation;
use App\Entity\Mention;
use App\Entity\Parcours;
use App\Entity\TypeDiplome;
use App\Entity\User;
use App\Enums\EtatDpeEnum;
use App\Enums\TypeModificationDpeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Parcours>
 *
 * @method Parcours|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parcours|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parcours[]    findAll()
 * @method Parcours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parcours::class);
    }

    public function save(Parcours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Parcours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByFormation(Formation $formation): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.formation = :formation')
            ->setParameter('formation', $formation)
            ->orderBy('p.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByTypeValidation(CampagneCollecte $campagneCollecte, mixed $typeValidation): array
    {
        $query = $this->createQueryBuilder('p')
            ->join('p.dpeParcours', 'dp')
            ->innerJoin('p.formation', 'f')
            ->andWhere("JSON_CONTAINS(dp.etatValidation, :etatDpe) = 1")
            ->setParameter('etatDpe', json_encode([$typeValidation => 1]))
            ->andWhere('dp.campagneCollecte = :campagneCollecte')
            ->setParameter('campagneCollecte', $campagneCollecte);

        return $query->getQuery()
            ->getResult();
    }

    public function findByTypeValidationAttenteCfvu(CampagneCollecte $campagneCollecte, mixed $typeValidation): array
    {
        $query = $this->createQueryBuilder('p')
            ->join('p.dpeParcours', 'dp')
            ->innerJoin('p.formation', 'f')
            ->andWhere("JSON_CONTAINS(dp.etatValidation, :etatDpe) = 1")
            ->andWhere('dp.etatReconduction = :etatReconduction')
            ->orWhere('dp.etatReconduction = :etatReconduction2')
            ->setParameter('etatDpe', json_encode([$typeValidation => 1]))
            ->setParameter('etatReconduction', TypeModificationDpeEnum::MODIFICATION_MCCC)
            ->setParameter('etatReconduction2', TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE)
            ->andWhere('dp.campagneCollecte = :campagneCollecte')
            ->setParameter('campagneCollecte', $campagneCollecte);

        return $query->getQuery()
            ->getResult();
    }

    public function findParcours(CampagneCollecte $campagneCollecte, array $options): array
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.dpeParcours', 'dp')
            ->where('p.libelle <> :libelle')
            ->setParameter('libelle', Parcours::PARCOURS_DEFAUT)
            ->innerJoin('p.formation', 'f')
            ->andWhere('dp.campagneCollecte = :campagneCollecte')
            ->setParameter('campagneCollecte', $campagneCollecte);

        foreach ($options as $sort => $direction) {
            if ($sort === 'recherche' && $direction !== '') {
                $qb->andWhere('p.libelle LIKE :recherche OR p.sigle LIKE :recherche')
                    ->setParameter('recherche', '%' . $direction . '%');
            } elseif ($sort === 'composante') {
                $qb->innerJoin('f.composantePorteuse', 'c')
                    ->addOrderBy('c.libelle', $direction);
            } elseif ($sort === 'mention') {
                $qb->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id');
                $qb->addOrderBy(
                    'CASE
                            WHEN f.mention IS NOT NULL THEN m.libelle
                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
                            ELSE f.mentionTexte
                            END',
                    $direction
                );
            } else {
                $qb->addOrderBy('p.' . $sort, $direction);
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function findRespOtherParcoursInFormation(Parcours $parcours, User $user): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.formation', 'f')
            ->andWhere('f = :formation')
            ->andWhere('p.respParcours = :user')
            ->andWhere('p.id <> :parcours')
            ->setParameter('formation', $parcours->getFormation())
            ->setParameter('user', $user)
            ->setParameter('parcours', $parcours)
            ->getQuery()
            ->getResult();
    }

    public function findAllParcoursId(){
        return $this->createQueryBuilder('p')
            ->select('p.id')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllParcours()
    {
        return $this->createQueryBuilder('p')
            ->join('p.formation', 'f')
            ->select('p.id', 'f.id as formation_id', 'f.f.etatDpe as etatDpe')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllParcoursForDpe(CampagneCollecte $campagneCollecte){
        return $this->createQueryBuilder('p')
            ->join('p.dpeParcours', 'dp')
            ->where('dp.campagneCollecte = :campagneCollecte')
            ->setParameter('campagneCollecte', $campagneCollecte)
            ->getQuery()
            ->getResult();

    }

    public function findByTypeValidationAttenteCfvuAndComposante(CampagneCollecte $campagneCollecte, string $typeValidation, Composante|int $composante)
    {
        if (!is_int($composante)) {
            $composante = $composante->getId();
        }

        $query = $this->createQueryBuilder('p')
            ->join('p.dpeParcours', 'dp')
            ->innerJoin('p.formation', 'f')
            ->andWhere('f.composantePorteuse = :composante')
            ->andWhere("JSON_CONTAINS(dp.etatValidation, :etatDpe) = 1")
            ->andWhere('dp.etatReconduction = :etatReconduction')
            ->orWhere('dp.etatReconduction = :etatReconduction2')
            ->setParameter('etatDpe', json_encode([$typeValidation => 1]))
            ->setParameter('composante', $composante)
            ->setParameter('etatReconduction', TypeModificationDpeEnum::MODIFICATION_MCCC)
            ->setParameter('etatReconduction2', TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE)
            ->andWhere('dp.campagneCollecte = :campagneCollecte')
            ->setParameter('campagneCollecte', $campagneCollecte);

        return $query->getQuery()
            ->getResult();

    }

    public function findByComposanteTypeValidation(Composante $composante,
                                                   CampagneCollecte $campagneCollecte,
                                                   string $typeValidation): array
    {
        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.formation', 'f')
            ->innerJoin(Composante::class, 'c', 'WITH', 'f.composantePorteuse = c.id')
            ->leftJoin('p.dpeParcours', 'dp')
            ->addSelect('dp')
            ->andWhere('c.id = :composante')
            ->andWhere("JSON_CONTAINS(dp.etatValidation, :etatDpe) = 1")
            ->setParameter('etatDpe', json_encode([$typeValidation => 1]))
            ->andWhere('dp.campagneCollecte = :campagneCollecte')
            ->setParameter('campagneCollecte', $campagneCollecte)
            ->setParameter('composante', $composante);


        return $query->getQuery()
            ->getResult();
    }

    public function findWithKeyword(string $keyword) {
        $qb = $this->createQueryBuilder('p');

        $parcoursParDefaut = Parcours::PARCOURS_DEFAUT;

        $qb = $qb
            ->select(
                [
                    'p.id AS parcours_id', 'p.libelle AS parcours_libelle',
                    'p.sigle AS parcours_sigle', 'p.objectifsParcours',
                    'p.poursuitesEtudes', 'p.contenuFormation',
                    'p.resultatsAttendus', 'f.id AS formation_id'
                ]
            )
            ->join('p.formation', 'f', 'WITH', 'p.formation = f.id')
            ->where(
                $qb->expr()->like('UPPER(p.objectifsParcours)', 'UPPER(:keyword)')
            )
            ->orWhere(
                $qb->expr()->like('UPPER(p.poursuitesEtudes)', 'UPPER(:keyword)')
            )
            ->orWhere(
                $qb->expr()->like('UPPER(p.contenuFormation)', 'UPPER(:keyword)')
            )
            ->orWhere(
                $qb->expr()->like('UPPER(p.resultatsAttendus)', 'UPPER(:keyword)')
            )
            ->andWhere("p.libelle != :parcoursParDefaut")
            ->setParameter('parcoursParDefaut', $parcoursParDefaut)
            ->setParameter('keyword', '%' . $keyword . '%');

        return $qb->getQuery()->getResult();
    }

    public function findWithKeywordForDefaultParcours(string $keyword){
        $qb = $this->createQueryBuilder('p');

        $parcoursParDefaut = Parcours::PARCOURS_DEFAUT;

        $qb = $qb
            ->join('p.formation', 'f', 'WITH', 'f.id = p.formation')
            ->select(
                [
                    'f.id AS formation_id', 'f.slug AS formation_slug', 'p.id AS parcours_id', 
                    'f.contenuFormation', 'f.resultatsAttendus', 'f.objectifsFormation', 
                    'p.poursuitesEtudes', 'p.libelle AS parcours_libelle', 'f.sigle AS formation_sigle'
                ]
            )
            ->where(
                $qb->expr()->like('UPPER(f.contenuFormation)', 'UPPER(:keyword)')
            )
            ->orWhere(
                $qb->expr()->like('UPPER(f.resultatsAttendus)', 'UPPER(:keyword)')
            )
            ->orWhere(
                $qb->expr()->like('UPPER(f.objectifsFormation)', 'UPPER(:keyword)')
            )->orWhere(
                $qb->expr()->like('UPPER(p.poursuitesEtudes)', 'UPPER(:keyword)')
            )
            ->andWhere('p.libelle = :parcoursParDefaut')
            ->setParameter('parcoursParDefaut', $parcoursParDefaut)
            ->setParameter('keyword', '%' . $keyword . '%');

            return $qb->getQuery()->getResult();
    }
}
