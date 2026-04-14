<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Service/Prototype/TypeDiplomePrototypeQueryService.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/04/2026 16:40
 */

declare(strict_types=1);

namespace App\Service\Prototype;

use App\Entity\TypeDiplome;
use App\Repository\TypeDiplomeRepository;

final class TypeDiplomePrototypeQueryService
{
    private const ALLOWED_LIMITS = [10, 25, 50, 100];

    private const ALLOWED_SORTS = [
        'libelle' => 'td.libelle',
        'libelle_court' => 'td.libelle_court',
        'codeApogee' => 'td.codeApogee',
        'semestreDebut' => 'td.semestreDebut',
        'semestreFin' => 'td.semestreFin',
        'id' => 'td.id',
    ];

    public function __construct(
        private readonly TypeDiplomeRepository $typeDiplomeRepository
    )
    {
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array{
     *     items: array<int, TypeDiplome>,
     *     total: int,
     *     pages: int,
     *     page: int,
     *     limit: int,
     *     sort: string,
     *     direction: string,
     *     q: string,
     *     hasStage: string,
     *     hasMemoire: string
     * }
     */
    public function search(array $params): array
    {
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = (int)($params['limit'] ?? 25);
        $limit = in_array($limit, self::ALLOWED_LIMITS, true) ? $limit : 25;

        $sort = (string)($params['sort'] ?? 'libelle');
        $sort = array_key_exists($sort, self::ALLOWED_SORTS) ? $sort : 'libelle';

        $direction = strtolower((string)($params['direction'] ?? 'asc'));
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        $q = trim((string)($params['q'] ?? ''));
        $hasStage = (string)($params['hasStage'] ?? '');
        $hasMemoire = (string)($params['hasMemoire'] ?? '');

        $qb = $this->typeDiplomeRepository->createQueryBuilder('td');

        if ($q !== '') {
            $qb
                ->andWhere('LOWER(td.libelle) LIKE :q OR LOWER(td.libelle_court) LIKE :q OR LOWER(td.codeApogee) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        $hasStageBool = $this->normalizeBooleanFilter($hasStage);
        if ($hasStageBool !== null) {
            $qb
                ->andWhere('td.hasStage = :hasStage')
                ->setParameter('hasStage', $hasStageBool);
        }

        $hasMemoireBool = $this->normalizeBooleanFilter($hasMemoire);
        if ($hasMemoireBool !== null) {
            $qb
                ->andWhere('td.hasMemoire = :hasMemoire')
                ->setParameter('hasMemoire', $hasMemoireBool);
        }

        $total = (int)(clone $qb)
            ->select('COUNT(td.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $pages = max(1, (int)ceil($total / $limit));
        $page = min($page, $pages);

        $qb
            ->orderBy(self::ALLOWED_SORTS[$sort], $direction)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        /** @var array<int, TypeDiplome> $items */
        $items = $qb->getQuery()->getResult();

        return [
            'items' => $items,
            'total' => $total,
            'pages' => $pages,
            'page' => $page,
            'limit' => $limit,
            'sort' => $sort,
            'direction' => $direction,
            'q' => $q,
            'hasStage' => $hasStage,
            'hasMemoire' => $hasMemoire,
        ];
    }

    private function normalizeBooleanFilter(string $value): ?bool
    {
        return match ($value) {
            '1' => true,
            '0' => false,
            default => null,
        };
    }
}

