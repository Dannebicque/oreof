<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/FormationRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Repository;

use App\Entity\AnneeUniversitaire;
use App\Entity\Composante;
use App\Entity\Formation;
use App\Entity\Mention;
use App\Entity\Parcours;
use App\Entity\TypeDiplome;
use App\Entity\User;
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

    public function findByComposanteDpe(
        UserInterface      $user,
        AnneeUniversitaire $anneeUniversitaire,
        array              $sorts = []
    ): array {
        $query = $this->createQueryBuilder('f')
            ->innerJoin(Composante::class, 'c', 'WITH', 'f.composantePorteuse = c.id')
            ->where('c.responsableDpe = :user')
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('user', $user)
            ->setParameter('anneeUniversitaire', $anneeUniversitaire);

        foreach ($sorts as $sort => $direction) {
            if ($sort === 'mention') {
                $query->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id');
                $query->addOrderBy(
                    'CASE
                            WHEN f.mention IS NOT NULL THEN m.libelle
                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
                            ELSE f.mentionTexte
                            END',
                    $direction
                );
            } else {
                $query->addOrderBy('f.' . $sort, $direction);
            }
        }

        return $query->getQuery()
            ->getResult();
    }

    public function findBySearch(
        string|null        $q,
        AnneeUniversitaire $anneeUniversitaire,
        array              $options = [],
        Composante|null    $composante = null,
    ): array {
        $sort = $options['sort'] ?? 'typeDiplome';
        $direction = $options['direction'] ?? 'ASC';

        $query = $this->createQueryBuilder('f')
            ->innerJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id')
            ->where('f.anneeUniversitaire = :anneeUniversitaire')
            ->andWhere('m.libelle LIKE :q or m.sigle LIKE :q or f.mentionTexte LIKE :q ')
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('f.' . $sort, $direction);

        if (array_key_exists('typeDiplome', $options) && null !== $options['typeDiplome']) {
            $query->andWhere('f.typeDiplome = :typeDiplome')
                ->setParameter('typeDiplome', $options['typeDiplome']);
        }

        if (array_key_exists('mention', $options) && null !== $options['mention']) {
            $query->andWhere('f.mention = :mention')
                ->setParameter('mention', $options['mention']);
        }

        if (array_key_exists('composantePorteuse', $options) && null !== $options['composantePorteuse']) {
            $query->andWhere('f.composantePorteuse = :composante')
                ->setParameter('composante', $options['composantePorteuse']);
        }

        if (array_key_exists('etatDpe', $options) && null !== $options['etatDpe']) {
            $query->andWhere("JSON_CONTAINS(f.etatDpe, :etatDpe) = 1")
                ->setParameter('etatDpe', json_encode([$options['etatDpe'] => 1]));
        }

        if ($sort === 'mention') {
            $query->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id');
            $query->addOrderBy(
                'CASE
                            WHEN f.mention IS NOT NULL THEN m.libelle
                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
                            ELSE f.mentionTexte
                            END',
                $direction
            );
        }

        if ($composante !== null) {
            $query->andWhere('f.composantePorteuse = :composante')
                ->setParameter('composante', $composante);
        }

        return $query->getQuery()
            ->getResult();
    }

    public function findBySearchAndCfvu(
        string|null        $q,
        AnneeUniversitaire $anneeUniversitaire,
        array              $options = [],
        Composante|null    $composante = null,
    ): array {
        $sort = $options['sort'] ?? 'typeDiplome';
        $direction = $options['direction'] ?? 'ASC';

        $query = $this->createQueryBuilder('f')
            ->innerJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id')
            ->where('f.anneeUniversitaire = :anneeUniversitaire')
            ->andWhere('m.libelle LIKE :q or m.sigle LIKE :q or f.mentionTexte LIKE :q ')
            ->andWhere("JSON_CONTAINS(f.etatDpe, :etatDpe) = 1")
            ->setParameter('etatDpe', json_encode(['soumis_cfvu' => 1]))
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('f.' . $sort, $direction);

        if (array_key_exists('typeDiplome', $options) && null !== $options['typeDiplome']) {
            $query->andWhere('f.typeDiplome = :typeDiplome')
                ->setParameter('typeDiplome', $options['typeDiplome']);
        }

        if (array_key_exists('mention', $options) && null !== $options['mention']) {
            $query->andWhere('f.mention = :mention')
                ->setParameter('mention', $options['mention']);
        }

        if (array_key_exists('composantePorteuse', $options) && null !== $options['composantePorteuse']) {
            $query->andWhere('f.composantePorteuse = :composante')
                ->setParameter('composante', $options['composantePorteuse']);
        }

        if ($sort === 'mention') {
            $query->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id');
            $query->addOrderBy(
                'CASE
                            WHEN f.mention IS NOT NULL THEN m.libelle
                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
                            ELSE f.mentionTexte
                            END',
                $direction
            );
        }

        if ($composante !== null) {
            $query->andWhere('f.composantePorteuse = :composante')
                ->setParameter('composante', $composante);
        }

        return $query->getQuery()
            ->getResult();
    }

    public function findByResponsableOuCoResponsable(User $user, AnneeUniversitaire $anneeUniversitaire, array $sorts = []): array
    {
        $query = $this->createQueryBuilder('f')
            ->where('f.responsableMention = :user')
            ->orWhere('f.coResponsable = :user')
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('user', $user)
            ->setParameter('anneeUniversitaire', $anneeUniversitaire);

        foreach ($sorts as $sort => $direction) {
            if ($sort === 'mention') {
                $query->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id');
                $query->addOrderBy(
                    'CASE
                            WHEN f.mention IS NOT NULL THEN m.libelle
                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
                            ELSE f.mentionTexte
                            END',
                    $direction
                );
            } else {
                $query->addOrderBy('f.' . $sort, $direction);
            }
        }

        return $query->getQuery()
            ->getResult();
    }

    public function findByComposante(Composante $composante, AnneeUniversitaire $anneeUniversitaire, array $sorts = []): array
    {
        $query = $this->createQueryBuilder('f')
            ->innerJoin(Composante::class, 'c', 'WITH', 'f.composantePorteuse = c.id')
            ->andWhere('c.id = :composante')
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->setParameter('composante', $composante);

        foreach ($sorts as $sort => $direction) {
            if ($sort === 'mention') {
                $query->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id');
                $query->addOrderBy(
                    'CASE
                            WHEN f.mention IS NOT NULL THEN m.libelle
                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
                            ELSE f.mentionTexte
                            END',
                    $direction
                );
            } else {
                $query->addOrderBy('f.' . $sort, $direction);
            }
        }

        return $query->getQuery()
            ->getResult();
    }

    public function findByComposanteTypeValidation(Composante $composante, AnneeUniversitaire $anneeUniversitaire, string $typeValidation): array
    {
        $query = $this->createQueryBuilder('f')
            ->innerJoin(Composante::class, 'c', 'WITH', 'f.composantePorteuse = c.id')
            ->andWhere('c.id = :composante')
            ->andWhere("JSON_CONTAINS(f.etatDpe, :etatDpe) = 1")
            ->setParameter('etatDpe', json_encode([$typeValidation => 1]))
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->setParameter('composante', $composante);


        return $query->getQuery()
            ->getResult();
    }

    public function findByResponsableOuCoResponsableParcours(?UserInterface $user, AnneeUniversitaire $anneeUniversitaire, array $sorts)
    {
        $query = $this->createQueryBuilder('f')
            ->innerJoin(Parcours::class, 'p', 'WITH', 'f.id = p.formation')
            ->where('p.respParcours = :user')
            ->orWhere('p.coResponsable = :user')
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('user', $user)
            ->setParameter('anneeUniversitaire', $anneeUniversitaire);

        foreach ($sorts as $sort => $direction) {
            if ($sort === 'mention') {
                $query->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id');
                $query->addOrderBy(
                    'CASE
                            WHEN f.mention IS NOT NULL THEN m.libelle
                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
                            ELSE f.mentionTexte
                            END',
                    $direction
                );
            } else {
                $query->addOrderBy('f.' . $sort, $direction);
            }
        }

        return $query->getQuery()
            ->getResult();
    }

    public function findByComposantePorteuse(mixed $composante): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.composantePorteuse = :composante')
            ->setParameter('composante', $composante)
            ->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id')
            ->addOrderBy(
                'CASE
                            WHEN f.mention IS NOT NULL THEN m.libelle
                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
                            ELSE f.mentionTexte
                            END',
                'ASC'
            )->getQuery()
            ->getResult();
    }

    public function findByComposanteAndAnneeUniversitaire(string|null $composante, string|null $anneeUniversitaire): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.composantePorteuse = :composante')
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('composante', $composante)
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id')
            ->addOrderBy(
                'CASE
                            WHEN f.mention IS NOT NULL THEN m.libelle
                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
                            ELSE f.mentionTexte
                            END',
                'ASC'
            )->getQuery()
            ->getResult();
    }

    public function findByComposanteAndAnneeUniversitaireAndTypeDiplome(string|null $composante, string|null $anneeUniversitaire, string|null $typeDiplome): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.composantePorteuse = :composante')
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->andWhere('f.typeDiplome = :typeDiplome')
            ->setParameter('composante', $composante)
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->setParameter('typeDiplome', $typeDiplome)
            ->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id')
            ->addOrderBy(
                'CASE
                            WHEN f.mention IS NOT NULL THEN m.libelle
                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
                            ELSE f.mentionTexte
                            END',
                'ASC'
            )->getQuery()
            ->getResult();
    }

    public function findByAnneeUniversitaireAndTypeDiplome(AnneeUniversitaire $anneeUniversitaire, TypeDiplome $typeDiplome): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->andWhere('f.typeDiplome = :typeDiplome')
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->setParameter('typeDiplome', $typeDiplome)
            ->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id')
            ->addOrderBy(
                'CASE
                            WHEN f.mention IS NOT NULL THEN m.libelle
                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
                            ELSE f.mentionTexte
                            END',
                'ASC'
            )->getQuery()
            ->getResult();
    }

    public function findByComposanteCfvu(Composante $composante, AnneeUniversitaire $anneeUniversitaire): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.composantePorteuse = :composante')
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->andWhere("JSON_CONTAINS(f.etatDpe, :etatDpe) = 1")
            ->setParameter('etatDpe', json_encode(['soumis_cfvu' => 1]))
            ->setParameter('composante', $composante)
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id')
            ->addOrderBy(
                'CASE
                            WHEN f.mention IS NOT NULL THEN m.libelle
                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
                            ELSE f.mentionTexte
                            END',
                'ASC'
            )->getQuery()
            ->getResult();
    }

    public function findByTypeValidation(AnneeUniversitaire $anneeUniversitaire, mixed $typeValidation): array
    {
        $query = $this->createQueryBuilder('f')
            ->innerJoin(Composante::class, 'c', 'WITH', 'f.composantePorteuse = c.id')
            ->andWhere("JSON_CONTAINS(f.etatDpe, :etatDpe) = 1")
            ->setParameter('etatDpe', json_encode([$typeValidation => 1]))
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('anneeUniversitaire', $anneeUniversitaire);


        return $query->getQuery()
            ->getResult();
    }
}
