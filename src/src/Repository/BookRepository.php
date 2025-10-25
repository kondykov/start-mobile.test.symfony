<?php

namespace App\Repository;

use App\Entity\Author;
use App\Entity\Book;
use App\Utils\PaginatedData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class BookRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findByAuthor(Author $author, int $page = 1, int $pageSize = 10): PaginatedData
    {
        $qb = $this->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC')
            ->where('b.author = :author')
            ->setParameter('author', $author);

        return $this->paginate($qb, $page, $pageSize);
    }

    public function findByAuthorAndTitle(Author $author, string $title, int $page = 1, int $pageSize = 10): ?object
    {
        $qb = $this->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC')
            ->where('b.author = :author')
            ->andWhere('b.title = :title')
            ->setParameter('author', $author)
            ->setParameter('title', $title);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
