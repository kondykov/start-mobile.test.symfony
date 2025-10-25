<?php

namespace App\Service\Extractor;

interface ExtractorInterface
{
    function extract(object $object): array;
}
