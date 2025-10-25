<?php

namespace App\Service;

use App\Entity\Author;
use App\Exception\ValidationException;
use App\Repository\AuthorRepository;
use App\Service\Interface\AuthorServiceInterface;
use App\Utils\PaginatedData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;


readonly class AuthorService implements AuthorServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface     $validator,
        private AuthorRepository       $repository,
    )
    {
    }

    function getAll(int $page = 1, int $limit = 10): PaginatedData
    {
        return $this->repository->findPaginated($page, $limit);
    }

    function findById(mixed $id): ?Author
    {
        return $this->repository->find($id);
    }

    /**
     * @throws ValidationException
     */
    function add(array $data): Author
    {
        $this->validate($data);

        $existingAuthor = $this->repository->findOneBy(['name' => $data['name']]);
        if ($existingAuthor) {
            throw new ValidationException([
                'author' => 'This author already exists'
            ]);
        }

        $author = new Author();
        $author->setName($data['name']);

        $this->em->persist($author);
        $this->em->flush();

        return $author;
    }

    function remove(int $id): bool
    {
        $author = $this->getById($id);
        $this->em->remove($author);
        $this->em->flush();

        return true;
    }

    function getById(int $id): Author
    {
        $author = $this->findById($id);

        if (!$author) {
            throw new NotFoundHttpException('Author not found');
        }

        return $author;
    }

    /**
     * @throws ValidationException
     */
    function update(int $authorId, array $data): Author
    {
        $author = $this->getById($authorId);

        $this->validate($data);

        $author->setName($data['name']);

        $this->em->persist($author);
        $this->em->flush();

        return $author;
    }

    /**
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    private function validate(array $data): void
    {
        $constraints = new Assert\Collection([
            'name' => new Assert\Required([
                new Assert\NotBlank(['message' => 'Name cannot be empty']),
                new Assert\Length([
                    'min' => 2,
                    'minMessage' => 'Name must be at least {{ limit }} characters'
                ]),
                new Assert\Type(['type' => 'string', 'message' => 'Name must be a string']),
            ]),
        ], null, null, true);

        $violations = $this->validator->validate($data, $constraints);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $field = trim($violation->getPropertyPath(), '[]');
                $errors[$field][] = $violation->getMessage();
            }
            throw new ValidationException($errors, $data);
        }
    }
}
