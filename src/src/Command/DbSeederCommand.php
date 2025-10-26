<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:seed', description: 'Seeding database')]
class DbSeederCommand extends Command
{
    private const BATCH_SIZE = 1000;
    private const TARGET_BOOKS_PER_AUTHOR = 100000;

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Fixed Entity Manager Seeding');

        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $authors = $this->setupAuthors($io);
        $this->seedBooksFixed($io, $authors);

        return Command::SUCCESS;
    }

    private function setupAuthors(SymfonyStyle $io): array
    {
        $io->section('Setting up authors');

        $authorNames = ['Толстой', 'Достоевский', 'Чехов', 'Пушкин'];
        $authors = [];

        foreach ($authorNames as $name) {
            $author = $this->em->getRepository(Author::class)->findOneBy(['name' => $name]);

            if (!$author) {
                $author = new Author();
                $author->setName($name);
                $this->em->persist($author);
                $this->em->flush();
                $io->writeln("Created author: <info>{$name}</info>");
            } else {
                $existingBooks = $this->em->getRepository(Book::class)
                    ->count(['author' => $author]);

                $io->writeln(sprintf(
                    "Author: <info>%s</info> (has: <comment>%d</comment> books)",
                    $name,
                    $existingBooks
                ));
            }

            $authors[] = [
                'id' => $author->getId(),
                'name' => $author->getName(),
                'existing_books' => $existingBooks ?? 0
            ];
        }

        return $authors;
    }

    private function seedBooksFixed(SymfonyStyle $io, array $authors): void
    {
        $io->section('Seeding books with proper entity management');

        $totalCreated = 0;
        $startTime = microtime(true);

        foreach ($authors as $authorData) {
            $authorId = $authorData['id'];
            $authorName = $authorData['name'];
            $existingBooks = $authorData['existing_books'];

            $neededBooks = self::TARGET_BOOKS_PER_AUTHOR - $existingBooks;

            if ($neededBooks <= 0) {
                $io->success(sprintf('✓ %s already has %d books', $authorName, $existingBooks));
                continue;
            }

            $io->section(sprintf(
                'Seeding %s: %d books needed',
                $authorName,
                $neededBooks
            ));

            $createdForAuthor = 0;
            $batchCount = 0;

            $author = $this->em->getRepository(Author::class)->find($authorId);

            for ($i = $existingBooks + 1; $i <= self::TARGET_BOOKS_PER_AUTHOR; $i++) {
                $book = new Book();
                $book->setTitle($this->generateUniqueTitle($authorName, $i));
                $book->setAuthor($author);

                $this->em->persist($book);
                $batchCount++;
                $createdForAuthor++;

                if ($batchCount >= self::BATCH_SIZE || $i === self::TARGET_BOOKS_PER_AUTHOR) {
                    $this->em->flush();
                    $this->em->clear();

                    $totalCreated += $batchCount;

                    $progress = round(($i / self::TARGET_BOOKS_PER_AUTHOR) * 100, 1);
                    $io->writeln(sprintf(
                        '  Progress: %d/%d (%s%%) - Batch: %d books',
                        $i,
                        self::TARGET_BOOKS_PER_AUTHOR,
                        $progress,
                        $batchCount
                    ));

                    $batchCount = 0;

                    $author = $this->em->getRepository(Author::class)->find($authorId);

                    if ($i % 5000 === 0) {
                        gc_collect_cycles();
                    }
                }
            }

            $io->success(sprintf(
                '✅ %s: +%d books (total: %d)',
                $authorName,
                $createdForAuthor,
                $existingBooks + $createdForAuthor
            ));
        }

        $totalTime = round(microtime(true) - $startTime, 2);
        $booksPerSecond = round($totalCreated / $totalTime, 2);

        $io->success([
            sprintf('Total books created: %d', $totalCreated),
            sprintf('Time: %s seconds', $totalTime),
            sprintf('Speed: %s books/second', $booksPerSecond),
        ]);
    }

    private function generateUniqueTitle(string $authorName, int $number): string
    {
        $variants = [
            "Книга {$number} автора {$authorName}",
            "Том {$number}. {$authorName}",
            "Произведение №{$number} - {$authorName}",
            "Издание {$number} ({$authorName})",
            "{$authorName} - Работа {$number}",
        ];

        return $variants[array_rand($variants)];
    }
}
