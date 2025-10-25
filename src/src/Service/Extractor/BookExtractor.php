<?php

namespace App\Service\Extractor;

use App\Entity\Book;

class BookExtractor implements ExtractorInterface
{

    /** @var Book $object */
    function extract(object $object): array
    {
        return [
            'id' => $object->getId(),

            'title' => $object->getTitle(),
            'author' => $object->getAuthor()->getName(),

            'created_at' => $object->getCreatedAt()->format("Y-m-d H:i:s"),
            'updated_at' => $object->getUpdatedAt()->format("Y-m-d H:i:s"),
        ];
    }
}
