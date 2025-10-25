<?php

namespace App\Service\Interface;

use App\Entity\Author;
use App\Entity\Book;
use App\Utils\PaginatedData;

interface BookServiceInterface
{
    function findById(int $id): ?Book;
    function findByAuthor(Author $author): PaginatedData;
    function getById(int $id): Book;
    function getAll(int $page = 1, int $pageSize = 20): PaginatedData;
    function add(array $data): Book;
    function update(Book $book, array $data): Book;
    function remove(int $bookId): true;
}
