<?php

namespace App\Repository;

use App\Utils\PaginatedData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

abstract class AbstractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function findPaginated(int $page = 1, int $limit = 20): PaginatedData
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.createdAt', 'DESC');

        return $this->paginate($qb, $page, $limit);
    }

    protected function paginate(QueryBuilder $qb, int $page, int $limit): PaginatedData
    {
        $paginator = new Paginator($qb);
        $paginator
            ->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $data = iterator_to_array($paginator);
        $total = count($paginator);

        return new PaginatedData($data, $page, $limit, $total);
    }
}
