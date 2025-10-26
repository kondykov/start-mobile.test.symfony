<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Service\Extractor\BookExtractor;
use App\Service\Interface\BookServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class BookController extends AbstractController
{
    public function __construct(
        private readonly BookExtractor        $extractor,
        private readonly BookServiceInterface $service,
    )
    {
    }

    #[Route('api/v1/books', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 40);
        $collection = $this->service->getAll($page, $pageSize);

        return $this->json([
            'books' => array_map(
                [$this->extractor, 'extract'],
                $collection->getData(),
            ),
            'pagination' => $collection->getMetadata(),
        ]);
    }

    #[Route('api/v1/books/{id}', methods: ['GET'])]
    public function show(int $id): Response
    {
        return $this->json([
            'data' => $this->extractor->extract($this->service->getById($id)),
        ]);
    }

    #[Route('api/v1/books', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $data = $request->request->all();

        return $this->json([
            'data' => $this->extractor->extract($this->service->add($data)),
        ]);
    }

    #[Route('api/v1/books/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): Response
    {
        $data = $request->request->all();
        $book = $this->service->getById($id);

        return $this->json([
            'data' => $this->extractor->extract($this->service->update($book, $data)),
        ]);
    }

    #[Route('api/v1/books/{id}', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $this->service->remove($id);

        return $this->json(null, 204);
    }
}
