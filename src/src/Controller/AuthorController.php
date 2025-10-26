<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\BookRepository;
use App\Service\Interface\AuthorServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthorController extends AbstractController
{
    public function __construct(
        private readonly AuthorServiceInterface $authorService,
        private readonly BookRepository         $bookRepository,
    )
    {
    }

    #[Route('/authors', name: 'authors', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 40);
        $collection = $this->authorService->getAll($page, $pageSize);

        return $this->render('admin/author/index.html.twig', [
            'authors' => $collection->getData(),
            'pagination' => $collection->getMetadata(),
        ]);
    }

    #[Route('/authors/new', name: 'author_create', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('admin/author/create.html.twig');
    }

    #[Route('/authors', name: 'author_store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $data = $request->request->all();
        $this->authorService->add($data);
        return $this->redirectToRoute('authors');
    }

    #[Route('/authors/{id}', name: 'author_show', methods: ['GET'])]
    public function show(Request $request, int $id): Response
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 40);
        $author = $this->authorService->getById($id);
        $booksCollection = $this->bookRepository->findByAuthor($author, $page, $pageSize);

        return $this->render('admin/author/show.html.twig', [
            'author' => $author,
            'books' => $booksCollection->getData(),
            'pagination' => $booksCollection->getMetadata(),
        ]);
    }

    #[Route('/authors/{id}/edit', name: 'author_edit', methods: ['GET'])]
    public function edit(Request $request, int $id): Response
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 40);
        $author = $this->authorService->getById($id);
        $booksCollection = $this->bookRepository->findByAuthor($author, $page, $pageSize);

        return $this->render('admin/author/edit.html.twig', [
            'author' => $author,
            'books' => $booksCollection->getData(),
            'pagination' => $booksCollection->getMetadata(),
        ]);
    }

    #[Route('/authors/{id}', name: 'author_update', methods: ['PUT'])]
    public function update(Request $request, int $id): Response
    {
        $this->authorService->update($id, $request->request->all());
        return $this->redirectToRoute('author_show', ['id' => $id]);
    }

    #[Route('/authors/{id}', name: 'author_delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->redirectToRoute('author_show', ['id' => $id]);
    }
}
