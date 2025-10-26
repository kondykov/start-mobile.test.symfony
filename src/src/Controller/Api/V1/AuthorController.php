<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Service\Extractor\AuthorExtractor;
use App\Service\Interface\AuthorServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthorController extends AbstractController
{
    public function __construct(
        private readonly AuthorServiceInterface $service,
        private readonly AuthorExtractor        $extractor,
    )
    {
    }

    #[Route('api/v1/authors', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 40);

        $collection = $this->service->getAll($page, $pageSize);

        return $this->json([
            'data' => array_map(
                [$this->extractor, 'extract'],
                $collection->getData(),
            ),
            'pagination' => $collection->getMetadata(),
        ]);
    }

    #[Route('api/v1/authors/{id}', methods: ['GET'])]
    public function show(int $id): Response
    {
        return $this->json([
            'data' => $this->extractor->extract($this->service->getById($id)),
        ]);
    }

    #[Route('api/v1/authors', methods: ['POST'])]
    public function store(Request $request): Response
    {
        return $this->json([
            'data' => $this->extractor->extract($this->service->add($request->request->all())),
        ], 201);
    }

    #[Route('api/v1/authors/{id}', methods: ['PUT'])]
    public function update(Request $request, mixed $id): Response
    {
        return $this->json([
            'data' => $this->extractor->extract($this->service->update($id, $request->request->all())),
        ]);
    }

    #[Route('api/v1/authors/{id}', methods: ['DELETE'])]
    public function delete(mixed $id): Response
    {
        $this->service->remove($id);

        return $this->json(null, status: 204);
    }
}
