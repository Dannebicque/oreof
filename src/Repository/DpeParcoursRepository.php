<?php

namespace App\Repository;

use App\Entity\Composante;
use App\Entity\CampagneCollecte;
use App\Entity\DpeParcours;
use App\Entity\Mention;
use App\Entity\Parcours;
use App\Enums\EtatDpeEnum;
use App\Enums\TypeModificationDpeEnum;
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

    public function findByComposanteAndCampagneAndTypeValidation(Composante $composante, CampagneCollecte $campagneCollecte, string $typeValidation): array
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
            ->andWhere("JSON_CONTAINS(d.etatValidation, :etatDpe) = 1")
            ->setParameter('etatDpe', json_encode([$typeValidation => 1]))
            ->addOrderBy('m.libelle', 'ASC')
            ->addOrderBy('f.mentionTexte', 'ASC')
        ;

        return $query->getQuery()
            ->getResult();
    }

    public function findByCampagneAndTypeValidation(CampagneCollecte $campagneCollecte, string $typeValidation): array
    {
        $query = $this->createQueryBuilder('d')
            ->innerJoin('d.formation', 'f')
            ->addSelect('f')
            ->innerJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id')
            ->where('d.campagneCollecte = :campagneCollecte')
            ->setParameter('campagneCollecte', $campagneCollecte)
            ->orderBy('f.typeDiplome', 'ASC')
            ->andWhere("JSON_CONTAINS(d.etatValidation, :etatDpe) = 1")
            ->setParameter('etatDpe', json_encode([$typeValidation => 1]))
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

    public function findLastDpeForParcours(Parcours $objet): ?DpeParcours
    {
        $query = $this->createQueryBuilder('d')
            ->innerJoin('d.parcours', 'p')
            ->addSelect('p')
            ->where('p.id = :parcours')
            ->setParameter('parcours', $objet)
            ->orderBy('d.created', 'DESC')
            ->setMaxResults(1);

        return $query->getQuery()
            ->getOneOrNullResult();
    }

    public function findParcoursByComposante(CampagneCollecte $getDpe, Composante $composante): array
    {
        $query = $this->createQueryBuilder('d')
            ->innerJoin('d.parcours', 'p')
            ->addSelect('p')
            ->innerJoin('p.formation', 'f')
            ->addSelect('f')
            ->where('d.campagneCollecte = :campagneCollecte')
            ->andWhere('f.composantePorteuse = :composante')
//            ->andWhere('d.etatReconduction = :etatReconduction')
//            ->orWhere('d.etatReconduction = :etatReconduction2')
//            ->setParameter('etatReconduction', TypeModificationDpeEnum::MODIFICATION_MCCC)
//            ->setParameter('etatReconduction2', TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE)
            ->setParameter('campagneCollecte', $getDpe)
            ->setParameter('composante', $composante)
            ->orderBy('f.typeDiplome', 'ASC')
            ->addOrderBy('f.mentionTexte', 'ASC')
            ;

        return $query->getQuery()
            ->getResult();
    }

    public function findParcoursByComposanteCfvu(CampagneCollecte $getDpe, Composante $composante): array
    {
        $query = $this->createQueryBuilder('d')
            ->innerJoin('d.parcours', 'p')
            ->addSelect('p')
            ->innerJoin('p.formation', 'f')
            ->addSelect('f')
            ->where('d.campagneCollecte = :campagneCollecte')
            ->andWhere("JSON_CONTAINS(d.etatValidation, :etatDpe) = 1")
            ->setParameter('etatDpe', json_encode(['soumis_cfvu' => 1]))
            ->andWhere('f.composantePorteuse = :composante')
            ->andWhere('d.etatReconduction = :etatReconduction')
            ->orWhere('d.etatReconduction = :etatReconduction2')
            ->setParameter('etatReconduction', TypeModificationDpeEnum::MODIFICATION_MCCC)
            ->setParameter('etatReconduction2', TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE)
            ->setParameter('campagneCollecte', $getDpe)
            ->setParameter('composante', $composante)
            ->orderBy('f.typeDiplome', 'ASC')
            ->addOrderBy('f.mentionTexte', 'ASC')
        ;

        return $query->getQuery()
            ->getResult();
    }


    public function findByCampagneWithModification(CampagneCollecte $getDpe)
    {
        $query = $this->createQueryBuilder('d')
            ->innerJoin('d.formation', 'f')
            ->addSelect('f')
            ->where('d.campagneCollecte = :campagneCollecte')
            ->andWhere('d.etatReconduction = :etatReconduction')
            ->orWhere('d.etatReconduction = :etatReconduction2')
            ->setParameter('etatReconduction', TypeModificationDpeEnum::MODIFICATION_MCCC)
            ->setParameter('etatReconduction2', TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE)
            ->setParameter('campagneCollecte', $getDpe)
            ->orderBy('f.typeDiplome', 'ASC')
            ->addOrderBy('f.mentionTexte', 'ASC')
        ;

        return $query->getQuery()
            ->getResult();
    }
}
