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
            ->addOrderBy('f.mentionTexte', 'ASC')
            ;

        return $query->getQuery()
            ->getResult();
    }

    public function duplicateParcours(CampagneCollecte $campagneCollectePrecedente, CampagneCollecte $campagneCollecte): void
    {
        $parcours = $this->findBy(['campagneCollecte' => $campagneCollectePrecedente]);
        foreach ($parcours as $p) {
            $version = (int)(explode('.', $p->getVersion())[0]) + 1;
            $newParcours = new DpeParcours();
            $newParcours->setParcours($p->getParcours());
            $newParcours->setFormation($p->getFormation());
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
