<?php

namespace App\Service;

use App\Entity\Book;
use App\Repository\BookRepository;

/**
 * Сервис для форматирования данных о книгах.
 */
class BookDataFormatterService
{
    /**
     * @var BookRepository Репозиторий книг.
     */
    private BookRepository $bookRepository;

    public function __construct(BookRepository $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    /**
     * Форматирует данные всех книг.
     *
     * @return array Массив данных о книгах.
     */
    public function formatBooksData(): array
    {
        $books = $this->bookRepository->findAll();
        $data = [];
        foreach ($books as $book) {
            $data[] = $this->formatBookData($book);
        }
        return $data;
    }

    /**
     * Форматирует данные о книге.
     *
     * @param Book $book Книга для форматирования данных.
     * @return array Массив данных о книге.
     */
    public function formatBookData(Book $book): array
    {
        return [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'publicationYear' => $book->getPublicationYear()
        ];
    }
}
