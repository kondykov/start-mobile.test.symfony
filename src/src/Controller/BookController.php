<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Interface\AuthorServiceInterface;
use App\Service\Interface\BookServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookController extends AbstractController
{
    public function __construct(
        private readonly BookServiceInterface   $bookService,
        private readonly AuthorServiceInterface $authorService
    )
    {
    }

    #[Route('books', name: 'books', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 40);
        $booksCollection = $this->bookService->getAll($page, $pageSize);

        return $this->render('admin/book/index.html.twig', [
            'books' => $booksCollection->getData(),
            'pagination' => $booksCollection->getMetadata(),
        ]);
    }

    #[Route('authors/{authorId}/books', name: 'book_add', methods: ['GET'])]
    public function new(int $authorId): Response
    {
        return $this->render('admin/author/addBook.html.twig', [
            'author' => $this->authorService->getById($authorId),
        ]);
    }

    #[Route('authors/{authorId}/books', name: 'book_store', methods: ['POST'])]
    public function store(Request $request, int $authorId): Response
    {
        $data = $request->request->all();
        $data['author'] = $authorId;

        $this->bookService->add($data);
        return $this->redirectToRoute('author_show', ['id' => $authorId]);
    }

    #[Route('authors/{authorId}/books/{bookId}', name: 'book_delete', methods: ['PUT'])]
    public function update(Request $request, int $authorId, int $bookId): Response
    {
        $book = $this->bookService->getById($bookId);
        $this->bookService->update($book, $request->request->all());
        return $this->redirectToRoute('author_show', ['id' => $authorId]);
    }

    #[Route('authors/{authorId}/books/{bookId}', name: 'book_delete', methods: ['DELETE'])]
    public function delete(int $authorId, int $bookId): Response
    {
        $this->bookService->remove($bookId);
        return $this->redirectToRoute('author_show', ['id' => $authorId]);
    }
}
