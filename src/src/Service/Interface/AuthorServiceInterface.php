<?php

namespace App\Service\Interface;

use App\Entity\Author;
use App\Utils\PaginatedData;

interface AuthorServiceInterface
{
    function getAll(int $page = 1, int $limit = 10): PaginatedData;
    function getById(int $id): Author;
    function findById(mixed $id): ?Author;
    function findByName(mixed $name): ?Author;
    function add(array $data): Author;
    function update(int $authorId, array $data): Author;
    function remove(int $id): bool;
}
