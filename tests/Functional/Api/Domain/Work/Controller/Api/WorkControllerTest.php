<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Tests\Functional\Api\Domain\Work\Controller\Api;

use App\Tests\Functional\Api\BaseApiTestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

class WorkControllerTest extends BaseApiTestCase
{
    public function testCreate(): void
    {
        $title = $this->uniqueName('API work create');
        $work = $this->createWork($title);

        $responseTitle = $this->getString($work, 'title');
        $statusId = $this->getInt($work, 'status', 'id');
        $typeId = $this->getInt($work, 'type', 'id');
        $authorId = $this->getInt($work, 'author', 'id');
        $supervisorId = $this->getInt($work, 'supervisor', 'id');
        $deadline = $this->getString($work, 'deadline');

        $this->assertSame($title, $responseTitle);
        $this->assertSame(1, $statusId);
        $this->assertSame(1, $typeId);
        $this->assertSame($this->getUserId(self::STUDENT), $authorId);
        $this->assertSame($this->getUserId(self::SUPERVISOR), $supervisorId);
        $this->assertStringStartsWith('2035-06-30', $deadline);
    }

    /**
     * @param array<string, mixed> $body
     * @param string[] $expectedProperties
     */
    #[DataProvider('provideCreateValidationFailedCases')]
    public function testCreateValidationFailed(array $body, array $expectedProperties): void
    {
        $data = $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/key/works/create',
            username: self::SUPERVISOR,
            body: $body
        );

        $this->assertResponseStatusCodeSame(400);

        $message = $this->getString($data, 'message');
        $errors = $this->getList($data, 'errors');
        $properties = array_map(fn (array $error): string => $this->getString($error, 'property'), $errors);

        $this->assertSame('Validation failed', $message);

        foreach ($expectedProperties as $expectedProperty) {
            $this->assertContains($expectedProperty, $properties);
        }
    }

    public function testCreateForbiddenForStudent(): void
    {
        $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/key/works/create',
            username: self::STUDENT,
            body: [
                'title' => 'Student work',
                'shortcut' => null,
                'statusId' => 1,
                'typeId' => 1,
                'authorId' => $this->getUserId(self::STUDENT),
                'opponentId' => null,
                'consultantId' => null,
                'deadline' => '2035-06-30',
                'deadlineProgram' => null,
                'categoriesIds' => []
            ]
        );

        $this->assertResponseStatusCodeSame(403);
    }

    #[DataProvider('provideDetailCases')]
    public function testDetail(string $username): void
    {
        $title = $this->uniqueName('API work detail');
        $work = $this->createWork($title);
        $workId = $this->getInt($work, 'id');

        $uri = sprintf('/api/key/works/%d/detail', $workId);
        $data = $this->requestApi(Request::METHOD_GET, $uri, $username);

        $this->assertResponseStatusCodeSame(200);

        $id = $this->getInt($data, 'id');
        $responseTitle = $this->getString($data, 'title');
        $authorId = $this->getInt($data, 'author', 'id');

        $this->assertSame($workId, $id);
        $this->assertSame($title, $responseTitle);
        $this->assertSame($this->getUserId(self::STUDENT), $authorId);
    }

    public function testDetailNotFound(): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/works/999999999/detail', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(404);

        $message = $this->getString($data, 'message');
        $this->assertSame('Resource not found', $message);
    }

    #[DataProvider('provideListContainsCreatedWorkCases')]
    public function testListContainsCreatedWork(string $username, string $type): void
    {
        $title = $this->uniqueName('API work list');
        $work = $this->createWork($title);
        $workId = $this->getInt($work, 'id');
        $this->refreshSearchIndexes();

        $uri = sprintf('/api/key/works/%s?limit=1000', $type);
        $data = $this->requestApi(Request::METHOD_GET, $uri, $username);

        $this->assertResponseStatusCodeSame(200);

        $success = $this->getBool($data, 'success');
        $count = $this->getInt($data, 'count');
        $result = $this->getList($data, 'result');
        $ids = $this->getIds($data, 'result');

        $this->assertTrue($success);
        $this->assertCount($count, $result);
        $this->assertContains($workId, $ids);
    }

    #[DataProvider('provideSearchCases')]
    public function testSearch(string $query): void
    {
        $title = $this->uniqueName('API work search');
        $work = $this->createWork($title);
        $workId = $this->getInt($work, 'id');
        $this->refreshSearchIndexes();

        $query = str_replace('{title}', rawurlencode($title), $query);
        $uri = sprintf('/api/key/works/supervisor?%s', $query);
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $totalCount = $this->getInt($data, 'totalCount');

        $this->assertSame([$workId], $ids);
        $this->assertSame(1, $totalCount);
    }

    public function testSearchByPartOfTitle(): void
    {
        $title = $this->uniqueName('API work part');
        $work = $this->createWork($title);
        $workId = $this->getInt($work, 'id');
        $this->refreshSearchIndexes();

        $suffix = mb_substr($title, -6);
        $search = mb_strtoupper($suffix);
        $uri = sprintf('/api/key/works/supervisor?search=%s', rawurlencode($search));
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $this->assertContains($workId, $ids);
    }

    public function testSearchNothingFound(): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/works/supervisor?search=zzz-no-work-zzz', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $totalCount = $this->getInt($data, 'totalCount');
        $result = $this->getList($data, 'result');

        $this->assertSame(0, $totalCount);
        $this->assertSame([], $result);
    }

    public function testListPagination(): void
    {
        $this->createWork($this->uniqueName('API work page'));
        $this->createWork($this->uniqueName('API work page'));
        $this->refreshSearchIndexes();

        $firstPage = $this->requestApi(Request::METHOD_GET, '/api/key/works/supervisor?search=API%20work%20page&limit=1&page=1', self::SUPERVISOR);
        $secondPage = $this->requestApi(Request::METHOD_GET, '/api/key/works/supervisor?search=API%20work%20page&limit=1&page=2', self::SUPERVISOR);
        $allPage = $this->requestApi(Request::METHOD_GET, '/api/key/works/supervisor?search=API%20work%20page&limit=2&page=1', self::SUPERVISOR);

        $firstIds = $this->getIds($firstPage, 'result');
        $secondIds = $this->getIds($secondPage, 'result');
        $allIds = $this->getIds($allPage, 'result');
        $totalCount = $this->getInt($firstPage, 'totalCount');

        $this->assertCount(1, $firstIds);
        $this->assertCount(1, $secondIds);
        $this->assertGreaterThanOrEqual(2, $totalCount);
        $this->assertSame($allIds, [...$firstIds, ...$secondIds]);
    }

    public function testListInvalidPageValidationFailed(): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/works/supervisor?page=0', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(400);

        $property = $this->getString($data, 'errors', 0, 'property');
        $this->assertSame('page', $property);
    }

    public function testListUnknownTypeNotFound(): void
    {
        $this->requestApi(Request::METHOD_GET, '/api/key/works/admin', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(404);
    }

    public static function provideCreateValidationFailedCases(): Generator
    {
        yield 'blank title and invalid deadline' => [
            [
                'title' => '',
                'shortcut' => null,
                'statusId' => 1,
                'typeId' => 1,
                'authorId' => 2,
                'opponentId' => null,
                'consultantId' => null,
                'deadline' => 'not-a-date',
                'deadlineProgram' => null,
                'categoriesIds' => []
            ],
            ['title', 'deadline']
        ];

        yield 'unknown status and type' => [
            [
                'title' => 'Work',
                'shortcut' => null,
                'statusId' => 999_999,
                'typeId' => 999_999,
                'authorId' => 2,
                'opponentId' => null,
                'consultantId' => null,
                'deadline' => '2035-06-30',
                'deadlineProgram' => null,
                'categoriesIds' => []
            ],
            ['statusId', 'typeId']
        ];

        yield 'author without student role' => [
            [
                'title' => 'Work',
                'shortcut' => null,
                'statusId' => 1,
                'typeId' => 1,
                'authorId' => 3,
                'opponentId' => null,
                'consultantId' => null,
                'deadline' => '2035-06-30',
                'deadlineProgram' => null,
                'categoriesIds' => []
            ],
            ['authorId']
        ];
    }

    public static function provideDetailCases(): Generator
    {
        yield 'supervisor' => [self::SUPERVISOR];
        yield 'author' => [self::STUDENT];
    }

    public static function provideListContainsCreatedWorkCases(): Generator
    {
        yield 'supervisor' => [self::SUPERVISOR, 'supervisor'];
        yield 'author' => [self::STUDENT, 'author'];
    }

    public static function provideSearchCases(): Generator
    {
        yield 'search' => ['search={title}'];
        yield 'title' => ['title={title}'];
        yield 'search with status' => ['search={title}&status=1'];
        yield 'search with type' => ['search={title}&type[]=1'];
    }
}
