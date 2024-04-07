<?php

namespace App\Service;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Сервис для создания книг.
 */
class BookCreatorService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Создает новую книгу на основе предоставленных данных.
     *
     * @param array $data Данные для создания книги.
     * @return Book|null Возвращает созданную книгу или null, если произошла ошибка.
     * @throws \InvalidArgumentException Если предоставленные данные некорректны.
     */
    public function createBook(array $data): ?Book
    {
        // Валидация входных данных
        $errors = $this->validateBookData($data);
        if ($errors) {
            throw new \InvalidArgumentException('Invalid book data: ' . implode(', ', $errors));
        }

        $book = new Book();
        $book->setTitle($data['title']);
        $book->setAuthor($data['author']);
        $book->setPublicationYear($data['publicationYear']);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $book;
    }

    /**
     * Проводит валидацию данных книги.
     *
     * @param array $data Данные книги для валидации.
     * @return array Массив с сообщениями об ошибках валидации.
     */
    private function validateBookData(array $data): array
    {
        $errors = [];

        if (!isset($data['title'])) {
            $errors[] = 'Title is required';
        }
        if (!isset($data['author'])) {
            $errors[] = 'Author is required';
        }
        if (!isset($data['publicationYear'])) {
            $errors[] = 'Publication year is required';
        }

        return $errors;
    }
}
