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

namespace App\Tests\Functional\Api\Domain\Task\Controller\Api;

use App\Tests\Functional\Api\BaseApiTestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

class TaskControllerTest extends BaseApiTestCase
{
    public function testCreate(): void
    {
        $work = $this->createWork($this->uniqueName('API task work'));
        $workId = $this->getInt($work, 'id');
        $name = $this->uniqueName('API task create');

        $task = $this->createTask($workId, $name);

        $responseName = $this->getString($task, 'name');
        $description = $this->getString($task, 'description');
        $complete = $this->getBool($task, 'complete');
        $active = $this->getBool($task, 'active');
        $deadline = $this->getString($task, 'deadline');
        $responseWorkId = $this->getInt($task, 'work', 'id');

        $this->assertSame($name, $responseName);
        $this->assertSame('API task description', $description);
        $this->assertFalse($complete);
        $this->assertTrue($active);
        $this->assertStringStartsWith('2035-05-31', $deadline);
        $this->assertSame($workId, $responseWorkId);
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
            uri: '/api/key/tasks/create',
            username: self::SUPERVISOR,
            body: $body
        );

        $this->assertResponseStatusCodeSame(400);

        $errors = $this->getList($data, 'errors');
        $properties = array_map(fn (array $error): string => $this->getString($error, 'property'), $errors);

        foreach ($expectedProperties as $expectedProperty) {
            $this->assertContains($expectedProperty, $properties);
        }
    }

    public function testCreateForbiddenForNotWorkSupervisor(): void
    {
        $work = $this->createWork($this->uniqueName('API task foreign work'));
        $workId = $this->getInt($work, 'id');

        $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/key/tasks/create',
            username: self::STUDENT,
            body: [
                'name' => 'Student task',
                'description' => null,
                'deadline' => '2035-05-31',
                'workId' => $workId
            ]
        );

        $this->assertResponseStatusCodeSame(403);

        $tasks = $this->requestApi(Request::METHOD_GET, $this->getWorkTaskListUri($workId), self::SUPERVISOR);
        $totalCount = $this->getInt($tasks, 'totalCount');
        $this->assertSame(0, $totalCount);
    }

    #[DataProvider('provideDetailCases')]
    public function testDetail(string $username): void
    {
        [$workId, $taskId, $name] = $this->createWorkWithTask();

        $data = $this->requestApi(Request::METHOD_GET, $this->getTaskUri($taskId, $workId), $username);

        $this->assertResponseStatusCodeSame(200);

        $id = $this->getInt($data, 'id');
        $responseName = $this->getString($data, 'name');

        $this->assertSame($taskId, $id);
        $this->assertSame($name, $responseName);
    }

    public function testDetailTaskFromAnotherWorkNotFound(): void
    {
        [, $taskId] = $this->createWorkWithTask();
        $anotherWork = $this->createWork($this->uniqueName('API task another work'));
        $anotherWorkId = $this->getInt($anotherWork, 'id');

        $this->requestApi(Request::METHOD_GET, $this->getTaskUri($taskId, $anotherWorkId), self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testDetailNotFound(): void
    {
        $work = $this->createWork($this->uniqueName('API task missing'));
        $workId = $this->getInt($work, 'id');

        $this->requestApi(Request::METHOD_GET, $this->getTaskUri(999_999_999, $workId), self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(404);
    }

    #[DataProvider('provideChangeStatusCases')]
    public function testChangeStatus(string $type, string $property, bool $expected): void
    {
        [$workId, $taskId] = $this->createWorkWithTask();
        $uri = sprintf('%s/change/%s', $this->getTaskUri($taskId, $workId), $type);

        $this->requestApi(Request::METHOD_PUT, $uri, self::SUPERVISOR);
        $this->assertResponseStatusCodeSame(204);

        $data = $this->requestApi(Request::METHOD_GET, $this->getTaskUri($taskId, $workId), self::SUPERVISOR);
        $value = $this->getBool($data, $property);

        $this->assertSame($expected, $value);
    }

    public function testChangeStatusToggle(): void
    {
        [$workId, $taskId] = $this->createWorkWithTask();
        $uri = sprintf('%s/change/complete', $this->getTaskUri($taskId, $workId));

        $this->requestApi(Request::METHOD_PUT, $uri, self::SUPERVISOR);
        $this->requestApi(Request::METHOD_PUT, $uri, self::SUPERVISOR);
        $this->assertResponseStatusCodeSame(204);

        $data = $this->requestApi(Request::METHOD_GET, $this->getTaskUri($taskId, $workId), self::SUPERVISOR);
        $complete = $this->getBool($data, 'complete');

        $this->assertFalse($complete);
    }

    public function testChangeStatusNotifyResetsNotification(): void
    {
        [$workId, $taskId] = $this->createWorkWithTask();
        $taskUri = $this->getTaskUri($taskId, $workId);
        $notifyUri = sprintf('%s/notify/complete', $taskUri);
        $changeUri = sprintf('%s/change/notify', $taskUri);

        $this->requestApi(Request::METHOD_PUT, $changeUri, self::SUPERVISOR);
        $this->assertResponseStatusCodeSame(204);

        $data = $this->requestApi(Request::METHOD_GET, $taskUri, self::SUPERVISOR);
        $notifyComplete = $this->getBool($data, 'notifyComplete');
        $this->assertFalse($notifyComplete);

        $this->requestApi(Request::METHOD_PUT, $notifyUri, self::STUDENT);
        $this->requestApi(Request::METHOD_PUT, $changeUri, self::SUPERVISOR);
        $this->assertResponseStatusCodeSame(204);

        $data = $this->requestApi(Request::METHOD_GET, $taskUri, self::SUPERVISOR);
        $notifyComplete = $this->getBool($data, 'notifyComplete');
        $this->assertFalse($notifyComplete);

        $this->requestApi(Request::METHOD_PUT, $notifyUri, self::STUDENT);
        $this->assertResponseStatusCodeSame(204);
    }

    public function testChangeStatusForbiddenForStudent(): void
    {
        [$workId, $taskId] = $this->createWorkWithTask();
        $uri = sprintf('%s/change/complete', $this->getTaskUri($taskId, $workId));

        $this->requestApi(Request::METHOD_PUT, $uri, self::STUDENT);
        $this->assertResponseStatusCodeSame(403);

        $data = $this->requestApi(Request::METHOD_GET, $this->getTaskUri($taskId, $workId), self::SUPERVISOR);
        $complete = $this->getBool($data, 'complete');
        $this->assertFalse($complete);
    }

    public function testNotifyComplete(): void
    {
        [$workId, $taskId] = $this->createWorkWithTask();
        $uri = sprintf('%s/notify/complete', $this->getTaskUri($taskId, $workId));

        $this->requestApi(Request::METHOD_PUT, $uri, self::STUDENT);
        $this->assertResponseStatusCodeSame(204);

        $data = $this->requestApi(Request::METHOD_GET, $this->getTaskUri($taskId, $workId), self::SUPERVISOR);
        $notifyComplete = $this->getBool($data, 'notifyComplete');
        $this->assertTrue($notifyComplete);

        $this->requestApi(Request::METHOD_PUT, $uri, self::STUDENT);
        $this->assertResponseStatusCodeSame(409);
    }

    public function testNotifyCompleteForbiddenForSupervisor(): void
    {
        [$workId, $taskId] = $this->createWorkWithTask();
        $uri = sprintf('%s/notify/complete', $this->getTaskUri($taskId, $workId));

        $this->requestApi(Request::METHOD_PUT, $uri, self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDelete(): void
    {
        [$workId, $taskId] = $this->createWorkWithTask();
        $uri = $this->getTaskUri($taskId, $workId);

        $this->requestApi(Request::METHOD_DELETE, $uri, self::STUDENT);
        $this->assertResponseStatusCodeSame(403);

        $this->requestApi(Request::METHOD_DELETE, $uri, self::SUPERVISOR);
        $this->assertResponseStatusCodeSame(204);

        $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testListOwnerSearch(): void
    {
        [, $taskId, $name] = $this->createWorkWithTask();
        $this->refreshSearchIndexes();

        $suffix = mb_substr($name, -6);
        $search = mb_strtoupper($suffix);
        $uri = sprintf('/api-platform/task/list-owner?search=%s', rawurlencode($search));
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $totalCount = $this->getInt($data, 'totalCount');
        $workTitle = $this->getString($data, 'result', 0, 'work', 'title');

        $this->assertSame([$taskId], $ids);
        $this->assertSame(1, $totalCount);
        $this->assertNotSame('', $workTitle);
    }

    public function testListOwnerSearchByDescription(): void
    {
        $work = $this->createWork($this->uniqueName('API task description work'));
        $workId = $this->getInt($work, 'id');
        $description = $this->uniqueName('Unique description');
        $task = $this->createTask(
            workId: $workId,
            name: 'API task by description',
            description: $description
        );
        $taskId = $this->getInt($task, 'id');
        $this->refreshSearchIndexes();

        $uri = sprintf('/api-platform/task/list-owner?search=%s', rawurlencode($description));
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $this->assertSame([$taskId], $ids);
    }

    public function testListOwnerSearchOnlyOwnTasks(): void
    {
        [, , $name] = $this->createWorkWithTask();
        $this->refreshSearchIndexes();

        $uri = sprintf('/api-platform/task/list-owner?search=%s', rawurlencode($name));
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::STUDENT);

        $this->assertResponseStatusCodeSame(200);

        $totalCount = $this->getInt($data, 'totalCount');
        $this->assertSame(0, $totalCount);
    }

    public function testListOwnerWithoutSearch(): void
    {
        [, $taskId] = $this->createWorkWithTask();

        $data = $this->requestApi(Request::METHOD_GET, '/api-platform/task/list-owner?limit=1', self::SUPERVISOR);
        $searchData = $this->requestApi(Request::METHOD_GET, '/api-platform/task/list-owner?limit=1&search=%20', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $searchIds = $this->getIds($searchData, 'result');
        $totalCount = $this->getInt($data, 'totalCount');
        $searchTotalCount = $this->getInt($searchData, 'totalCount');
        $numItemsPerPage = $this->getInt($data, 'numItemsPerPage');

        $this->assertSame([$taskId], $ids);
        $this->assertSame($ids, $searchIds);
        $this->assertSame($totalCount, $searchTotalCount);
        $this->assertSame(1, $numItemsPerPage);
    }

    public function testListOwnerPagination(): void
    {
        $work = $this->createWork($this->uniqueName('API task page work'));
        $workId = $this->getInt($work, 'id');
        $this->createTask($workId, 'API task page first');
        $this->createTask($workId, 'API task page second');

        $firstPage = $this->requestApi(Request::METHOD_GET, '/api-platform/task/list-owner?limit=1&page=1', self::SUPERVISOR);
        $secondPage = $this->requestApi(Request::METHOD_GET, '/api-platform/task/list-owner?limit=1&page=2', self::SUPERVISOR);
        $allPage = $this->requestApi(Request::METHOD_GET, '/api-platform/task/list-owner?limit=2&page=1', self::SUPERVISOR);

        $firstIds = $this->getIds($firstPage, 'result');
        $secondIds = $this->getIds($secondPage, 'result');
        $allIds = $this->getIds($allPage, 'result');

        $this->assertSame($allIds, [...$firstIds, ...$secondIds]);
    }

    public function testListSolver(): void
    {
        [, $taskId] = $this->createWorkWithTask();

        $data = $this->requestApi(Request::METHOD_GET, '/api-platform/task/list-solver?limit=1000', self::STUDENT);

        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $this->assertContains($taskId, $ids);
    }

    public function testListWork(): void
    {
        [$workId, $taskId] = $this->createWorkWithTask();

        $data = $this->requestApi(Request::METHOD_GET, $this->getWorkTaskListUri($workId), self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $totalCount = $this->getInt($data, 'totalCount');

        $this->assertSame([$taskId], $ids);
        $this->assertSame(1, $totalCount);
    }

    public function testDetailApiPlatform(): void
    {
        [$workId, $taskId, $name] = $this->createWorkWithTask();
        $uri = sprintf('/api-platform/task/work/%d/task/%d/detail', $workId, $taskId);

        $data = $this->requestApi(Request::METHOD_GET, $uri, self::STUDENT);

        $this->assertResponseStatusCodeSame(200);

        $id = $this->getInt($data, 'result', 'id');
        $responseName = $this->getString($data, 'result', 'name');

        $this->assertSame($taskId, $id);
        $this->assertSame($name, $responseName);
    }

    public static function provideCreateValidationFailedCases(): Generator
    {
        yield 'blank name and invalid deadline' => [
            ['name' => '', 'description' => null, 'deadline' => 'not-a-date', 'workId' => 1],
            ['name', 'deadline']
        ];

        yield 'unknown work' => [
            ['name' => 'Task', 'description' => null, 'deadline' => '2035-05-31', 'workId' => 999_999_999],
            ['workId']
        ];
    }

    public static function provideDetailCases(): Generator
    {
        yield 'supervisor' => [self::SUPERVISOR];
        yield 'author' => [self::STUDENT];
    }

    public static function provideChangeStatusCases(): Generator
    {
        yield 'complete' => ['complete', 'complete', true];
        yield 'active' => ['active', 'active', false];
    }

    /**
     * @return array<mixed>
     */
    private function createTask(int $workId, string $name, string $description = 'API task description'): array
    {
        $data = $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/key/tasks/create',
            username: self::SUPERVISOR,
            body: [
                'name' => $name,
                'description' => $description,
                'deadline' => '2035-05-31',
                'workId' => $workId
            ]
        );

        $this->assertResponseStatusCodeSame(201);

        return $this->getArray($data, 'result');
    }

    /**
     * @return array{int, int, string}
     */
    private function createWorkWithTask(): array
    {
        $work = $this->createWork($this->uniqueName('API task work'));
        $workId = $this->getInt($work, 'id');
        $name = $this->uniqueName('API task');
        $task = $this->createTask($workId, $name);
        $taskId = $this->getInt($task, 'id');

        return [$workId, $taskId, $name];
    }

    private function getTaskUri(int $taskId, int $workId): string
    {
        return sprintf('/api/key/tasks/%d/works/%d', $taskId, $workId);
    }

    private function getWorkTaskListUri(int $workId): string
    {
        return sprintf('/api-platform/task/work/%d/list', $workId);
    }
}
