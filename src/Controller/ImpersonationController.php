<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/ImpersonationController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/04/2026 15:23
 */

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration/impersonation')]
#[IsGranted('ROLE_ADMIN')]
class ImpersonationController extends BaseController
{
    private const USERS_PER_PAGE = 50;

    public function __construct(
        private readonly UserRepository $userRepository,
    )
    {
    }

    #[Route('', name: 'app_impersonation_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);

        $query = $this->userRepository->createQueryBuilder('u')
            ->andWhere('u.isDeleted = false')
            ->andWhere('u.isEnable = true');

        if (!empty($search)) {
            $query->andWhere(
                $query->expr()->orX(
                    $query->expr()->like('u.username', ':search'),
                    $query->expr()->like('u.email', ':search'),
                    $query->expr()->like('u.nom', ':search'),
                    $query->expr()->like('u.prenom', ':search')
                )
            )
                ->setParameter('search', '%' . $search . '%');
        }

        $query->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom', 'ASC');

        $total = count($query->getQuery()->getResult());
        $users = $query
            ->setFirstResult(($page - 1) * self::USERS_PER_PAGE)
            ->setMaxResults(self::USERS_PER_PAGE)
            ->getQuery()
            ->getResult();

        $totalPages = ceil($total / self::USERS_PER_PAGE);

        return $this->render('impersonation/list.html.twig', [
            'users' => $users,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    #[Route('/api/search', name: 'app_impersonation_api_search', methods: ['GET'])]
    public function apiSearch(Request $request): JsonResponse
    {
        $search = $request->query->get('q', '');

        if (strlen($search) < 2) {
            return $this->json([]);
        }

        $users = $this->userRepository->createQueryBuilder('u')
            ->andWhere('u.isDeleted = false')
            ->andWhere('u.isEnable = true')
            ->andWhere(
                $this->userRepository->createQueryBuilder('u')->expr()->orX(
                    $this->userRepository->createQueryBuilder('u')->expr()->like('u.username', ':search'),
                    $this->userRepository->createQueryBuilder('u')->expr()->like('u.email', ':search'),
                    $this->userRepository->createQueryBuilder('u')->expr()->like('u.nom', ':search'),
                    $this->userRepository->createQueryBuilder('u')->expr()->like('u.prenom', ':search')
                )
            )
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return $this->json(array_map(function (User $user) {
            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'display' => $user->getDisplay(),
                'email' => $user->getEmail(),
                'roles' => implode(', ', $user->getRoles()),
            ];
        }, $users));
    }
}







