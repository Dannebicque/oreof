<?php

namespace App\Repository;

use App\Entity\Composante;
use App\Entity\CampagneCollecte;
use App\Entity\DpeParcours;
use App\Entity\Mention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DpeParcours>
 *
 * @method DpeParcours|null find($id, $lockMode = null, $lockVersion = null)
 * @method DpeParcours|null findOneBy(array $criteria, array $orderBy = null)
 * @method DpeParcours[]    findAll()
 * @method DpeParcours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DpeParcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DpeParcours::class);
    }

//    public function findFormationBySearch(
//        string|null     $q,
//        Dpe             $dpe,
//        array           $options = [],
//        Composante|null $composante = null,
//    ): array {
//        $sort = $options['sort'] ?? 'typeDiplome';
//        $direction = $options['direction'] ?? 'ASC';
//
//        $query = $this->createQueryBuilder('d')
//            ->innerJoin('d.formation', 'f')
//            ->addSelect('f')
//            ->innerJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id')
//            ->where('d.id = :dpe')
//            ->andWhere('m.libelle LIKE :q or m.sigle LIKE :q or f.mentionTexte LIKE :q ')
//            ->setParameter('dpe', $dpe)
//            ->setParameter('q', '%' . $q . '%')
//            ->orderBy('f.' . $sort, $direction);
//
//        if (array_key_exists('typeDiplome', $options) && null !== $options['typeDiplome']) {
//            $query->andWhere('f.typeDiplome = :typeDiplome')
//                ->setParameter('typeDiplome', $options['typeDiplome']);
//        }
//
//        if (array_key_exists('mention', $options) && null !== $options['mention']) {
//            $query->andWhere('f.mention = :mention')
//                ->setParameter('mention', $options['mention']);
//        }
//
//        if (array_key_exists('composantePorteuse', $options) && null !== $options['composantePorteuse']) {
//            $query->andWhere('f.composantePorteuse = :composante')
//                ->setParameter('composante', $options['composantePorteuse']);
//        }
//
//        if (array_key_exists('etatDpe', $options) && null !== $options['etatDpe']) {
//            $query->andWhere("JSON_CONTAINS(f.etatDpe, :etatDpe) = 1")
//                ->setParameter('etatDpe', json_encode([$options['etatDpe'] => 1]));
//        }
//
//        if ($sort === 'mention') {
//            $query->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id');
//            $query->addOrderBy(
//                'CASE
//                            WHEN f.mention IS NOT NULL THEN m.libelle
//                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
//                            ELSE f.mentionTexte
//                            END',
//                $direction
//            );
//        }
//
//        if ($composante !== null) {
//            $query->andWhere('f.composantePorteuse = :composante')
//                ->setParameter('composante', $composante);
//        }
//
//        return $query->getQuery()
//            ->getResult();
//    }

    public function findByComposanteAndCampagne(Composante $composante, CampagneCollecte $campagneCollecte): array
    {
        $query = $this->createQueryBuilder('d')
            ->innerJoin('d.formation', 'f')
            ->addSelect('f')
            ->innerJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id')
            ->where('d.campagneCollecte = :campagneCollecte')
            ->andWhere('f.composantePorteuse = :composante')
            ->setParameter('campagneCollecte', $campagneCollecte)
            ->setParameter('composante', $composante)
            ->orderBy('f.typeDiplome', 'ASC')
            ->addOrderBy('m.libelle', 'ASC')
            ->addOrderBy('f.mentionTexte', 'ASC');

        return $query->getQuery()
            ->getResult();
    }

    public function duplicateParcours(CampagneCollecte $campagneCollectePrecedente, CampagneCollecte $campagneCollecte): void
    {
        $parcours = $this->findBy(['campagneCollecte' => $campagneCollectePrecedente]);
        foreach ($parcours as $p) {
            $version = (int)(explode('.', $p->getVersion())[0]) + 1;
            $newParcours = new DpeParcours();
            $newParcours->setCampagneCollecte($campagneCollecte);
            $newParcours->setVersion($version.'.0');
            $newParcours->setEtatValidation([]);
            $newParcours->setEtatReconduction(null);
            $newParcours->setCreated(new \DateTime());

            $this->_em->persist($newParcours);
        }
        $this->_em->flush();
    }
}
