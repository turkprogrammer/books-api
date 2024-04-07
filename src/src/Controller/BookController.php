<?php

namespace App\Controller;
use App\Entity\Book;
use App\Repository\BookRepository;
use App\Service\BookCreatorService;
use App\Service\BookDataFormatterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class BookController
 *
 * Контроллер для работы с книгами.
 */
class BookController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private BookRepository $bookRepository;
    private BookDataFormatterService $bookDataFormatter;

    private ValidatorInterface $validator;
    private BookCreatorService $bookCreator;

    /**
     * BookController constructor.
     * @param EntityManagerInterface $entityManager
     * @param BookRepository $bookRepository
     * @param BookDataFormatterService $bookDataFormatter
     * @param ValidatorInterface $validator
     * @param BookCreatorService $bookCreator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        BookRepository $bookRepository,
        BookDataFormatterService $bookDataFormatter,
        ValidatorInterface $validator,
        BookCreatorService $bookCreator
    ) {
        $this->entityManager = $entityManager;
        $this->bookRepository = $bookRepository;
        $this->bookDataFormatter = $bookDataFormatter;
        $this->validator = $validator;
        $this->bookCreator = $bookCreator;
    }

    /**
     * Возвращает список всех книг.
     *
     * @return JsonResponse
     */
    #[Route("/api/books", name: 'books_list', methods: ['GET'])]
    public function index(): JsonResponse
    {
        try {
            $booksData = $this->bookDataFormatter->formatBooksData();
            return $this->json($booksData, 200);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to retrieve books.'], 500);
        }
    }

    /**
     * Создает новую книгу.
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route("/api/books", name: 'book_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $book = $this->bookCreator->createBook($data);

            return $this->json([
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'publicationYear' => $book->getPublicationYear(),
            ], 201);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to create book.'], 500);
        }
    }

    /**
     * Возвращает информацию о книге по её идентификатору.
     *
     * @param int|null $id
     * @return JsonResponse
     */
    #[Route("/api/books/{id}", name: 'book_show', methods: ['GET'])]
    public function show(?int $id): JsonResponse
    {
        try {
            $book = $this->bookRepository->find($id);
            if (!$book) {
                throw new NotFoundHttpException('Book not found.');
            }
            return $this->json($this->bookDataFormatter->formatBookData($book), 200);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Обновляет информацию о книге.
     *
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     */
    #[Route("/api/books/{id}", name: 'book_update', methods: ['PUT'])]
    public function update(Request $request, ?int $id): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $book = $this->bookRepository->find($id);
            if (!$book) {
                throw new NotFoundHttpException('Book not found.');
            }
            $this->updateBook($book, $data);
            $this->entityManager->flush();

            $formattedBook = $this->bookDataFormatter->formatBookData($book);
            return $this->json($formattedBook, 200);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Удаляет книгу по её идентификатору.
     *
     * @param int|null $id
     * @return JsonResponse
     */
    #[Route("/api/books/{id}", name: 'book_delete', methods: ['DELETE'])]
    public function delete(?int $id): JsonResponse
    {
        try {

            $book = $this->bookRepository->find($id);
            if (!$book) {
                throw new NotFoundHttpException('Book not found.');
            }

            $this->entityManager->remove($book);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Book deleted successfully.'], Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Валидирует данные книги.
     *
     * @param array $data
     * @return array
     */
    private function validateBookData(array $data): array
    {
        $errors = [];
        if (!isset($data['title'])) {
            $errors[] = 'Title is required.';
        }
        if (!isset($data['author'])) {
            $errors[] = 'Author is required.';
        }
        if (!isset($data['publicationYear'])) {
            $errors[] = 'Publication year is required.';
        }

        return $errors;
    }

    /**
     * Обновляет данные книги.
     *
     * @param Book $book
     * @param array $data
     * @return void
     */
    private function updateBook(Book $book, array $data): void
    {
        if (isset($data['title'])) {
            $book->setTitle($data['title']);
        }
        if (isset($data['author'])) {
            $book->setAuthor($data['author']);
        }
        if (isset($data['publicationYear'])) {
            $book->setPublicationYear($data['publicationYear']);
        }
    }
}
