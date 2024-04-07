<?php

namespace App\Tests\Unit;

use App\Entity\Book;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\PantherTestCase;

/**
 * Class BookControllerTest
 *
 * Тесты для контроллера BookController.
 */
class BookControllerTest extends PantherTestCase
{
    /**
     * Тест для метода index.
     *
     * Проверяет успешность запроса на получение списка книг.
     */
    public function testIndex(): void
    {
        $client = static::createClient();

        // Отправляем GET-запрос на /api/books
        $client->request('GET', '/api/books');

        // Проверяем успешность запроса (должен быть статус 200)
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Ожидаемая книга
        $expectedBook = [
            'id' => 12,
            'title' => 'New Book',
            'author' => 'John Doe',
            'publicationYear' => 2022
        ];

        // Получаем фактический ответ в виде массива
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Проверяем, что фактический ответ содержит ожидаемую книгу
        $this->assertContains($expectedBook, $responseData);
    }

    /**
     * Тест для метода createBook.
     *
     * Проверяет успешное создание новой книги.
     */
    public function testCreateBook(): void
    {
        $client = static::createClient();

        // Отправляем POST-запрос на эндпоинт /api/books с данными о книге
        $client->request('POST', '/api/books', [], [], [], json_encode([
            'title' => 'Solid Book',
            'author' => 'Robert Martin',
            'publicationYear' => 2023
        ]));

        // Проверяем, что ответ успешен (статус код 201)
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        // Проверяем, что ответ содержит данные созданной книги в формате JSON
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('title', $responseData);
        $this->assertArrayHasKey('author', $responseData);
        $this->assertArrayHasKey('publicationYear', $responseData);
        $this->assertEquals('Solid Book', $responseData['title']);
        $this->assertEquals('Robert Martin', $responseData['author']);
        $this->assertEquals(2023, $responseData['publicationYear']);
    }

    /**
     * Тест для метода show.
     *
     * Проверяет успешное получение информации о конкретной книге.
     */
    public function testShow(): void
    {
        $client = static::createClient();

        // Посылаем GET-запрос на /api/books/{id}, где {id} - идентификатор книги
        $client->request('GET', '/api/books/12');

        // Проверяем успешность запроса (должен быть статус 200)
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Проверяем, что ответ содержит ожидаемые данные в формате JSON
        $expectedData = [
            'id' => 12,
            'title' => 'New Book',
            'author' => 'John Doe',
            'publicationYear' => 2022
        ];
        $actualData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($expectedData, $actualData);
    }

    /**
     * Тест для метода update.
     *
     * Проверяет успешное обновление информации о книге.
     */
    public function testUpdate(): void
    {
        $client = static::createClient();

        // Отправляем PUT-запрос на /api/books/{id} с данными для обновления книги
        $client->request(
            'PUT',
            '/api/books/1',
            [],
            [],
            [],
            json_encode([
                'title' => 'Updated Title Book',
                'author' => 'Updated Author',
                'publicationYear' => 2022
            ])
        );

        // Проверяем успешность запроса (должен быть статус 200)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Проверяем, что книга была успешно обновлена
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Updated Title Book', $responseData['title']);
        $this->assertEquals('Updated Author', $responseData['author']);
        $this->assertEquals(2022, $responseData['publicationYear']);
    }

    /**
     * Тест для метода delete.
     *
     * Проверяет успешное удаление книги.
     */
    public function testDelete(): void
    {
        $client = static::createClient();

        // Перед удалением книги, получим количество книг в базе данных
        $initialBookCount = $this->getBookCount($client);

        // Отправляем DELETE-запрос на /api/books/{id} с существующим ID книги
        $client->request('DELETE', '/api/books/10');

        // Проверяем успешность запроса (должен быть статус 204 No Content)
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // После удаления книги, снова получим количество книг в базе данных
        $finalBookCount = $this->getBookCount($client);

        // Проверяем, что количество книг уменьшилось на 1
        $this->assertEquals($initialBookCount - 1, $finalBookCount);
    }

    /**
     * Получает количество книг в базе данных.
     *
     * @param $client
     * @return int Количество книг в базе данных.
     */
    private function getBookCount($client): int
    {
        // Получаем EntityManager из контейнера зависимостей
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Получаем репозиторий для сущности Book
        $bookRepository = $entityManager->getRepository(Book::class);

        // Получаем количество книг в базе данных
        return count($bookRepository->findAll());
    }
}



