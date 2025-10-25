<?php

namespace App\Service\Extractor;

use App\Entity\Author;

class AuthorExtractor implements ExtractorInterface
{

    /**
     * @param Author $object
     * @return array
     */
    function extract(object $object): array
    {
        return [
            'id' => $object->getId(),

            'name' => $object->getName(),
            'books' => count($object->getBooks()),

            'created_at' => $object->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $object->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
