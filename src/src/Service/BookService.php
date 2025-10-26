<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Exception\ValidationException;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Utils\PaginatedData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;


readonly class BookService implements Interface\BookServiceInterface
{
    public function __construct(
        private BookRepository         $bookRepository,
        private AuthorRepository       $authorRepository,
        private EntityManagerInterface $em,
        private ValidatorInterface     $validator,
    )
    {
    }

    function findById(int $id): ?Book
    {
        return $this->bookRepository->find($id);
    }

    function findByAuthor(Author $author): PaginatedData
    {
        return $this->bookRepository->findByAuthor($author);
    }

    function getById(int $id): Book
    {
        $book = $this->findById($id);
        if (null === $book) {
            throw new NotFoundHttpException('Book not found');
        }
        return $book;
    }

    function getAll(int $page = 1, int $pageSize = 20): PaginatedData
    {
        return $this->bookRepository->findPaginated($page, $pageSize);
    }

    /**
     * @throws ValidationException
     */
    function add(array $data): Book
    {
        $this->validate($data);

        $author = null;

        if (is_int($data['author'] )){
            $author = $this->authorRepository->find($data['author']);
        } elseif (is_string($data['author'])){
            $author = $this->authorRepository->findOneBy(['name' => $data['author']]);
        } elseif ($data['author'] instanceof Author){
            $author = $data['author'];
        }

        if (!$author) {
            throw new NotFoundHttpException('Author not found');
        }

        $existsBooks = $this->bookRepository->findByAuthorAndTitle($author, $data['title']);
        if ($existsBooks) {
            throw new ValidationException(['Book with title and author already exists']);
        }

        $book = new Book();
        $book->setTitle($data['title']);
        $book->setAuthor($author);

        $this->em->persist($book);
        $this->em->flush();

        return $book;
    }

    /**
     * @throws ValidationException
     */
    function update(Book $book, array $data): Book
    {
        $this->validate($data);

        $book->setTitle($data['title']);

        $this->em->persist($book);
        $this->em->flush();

        return $book;
    }

    function remove(int $bookId): true
    {
        $book = $this->getById($bookId);
        $this->em->remove($book);
        $this->em->flush();
        return true;
    }

    /**
     * @throws ValidationException
     */
    private function validate(array $data): void
    {
        $constraints = new Assert\Collection([
            'title' => new Assert\Required([
                new Assert\NotBlank(['message' => 'Title cannot be empty']),
                new Assert\Length([
                    'min' => 2,
                    'minMessage' => 'Title must be at least {{ limit }} characters'
                ]),
                new Assert\Type(['type' => 'string', 'message' => 'Name must be a string']),
            ]),
            'author' => new Assert\NotBlank(['message' => 'Author cannot be empty']),
        ], null, null, true);

        $violations = $this->validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $field = trim($violation->getPropertyPath(), '[]');
                $errors[$field] = $violation->getMessage();
            }
            throw new ValidationException($errors, $data);
        }
    }


}
