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

namespace App\Tests\Functional\Api\Domain\SystemEvent\Controller\Api;

use App\Tests\Functional\Api\BaseApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class SystemEventControllerTest extends BaseApiTestCase
{
    public function testWorkCreateCreatesUnreadEvent(): void
    {
        $title = $this->uniqueName('API system event work');
        $this->createWork($title);

        $event = $this->findEventByTitle('unread', $title);
        $this->assertIsArray($event);

        $viewed = $this->getBool($event, 'viewed');
        $eventTitle = $this->getString($event, 'title');

        $this->assertFalse($viewed);
        $this->assertStringContainsString($title, $eventTitle);

        $readEvent = $this->findEventByTitle('read', $title);
        $this->assertNull($readEvent);
    }

    public function testViewed(): void
    {
        $title = $this->uniqueName('API system event viewed');
        $this->createWork($title);

        $event = $this->findEventByTitle('unread', $title);
        $this->assertIsArray($event);

        $eventId = $this->getInt($event, 'id');
        $uri = sprintf('/api/key/system-events/%d/viewed', $eventId);

        $this->requestApi(Request::METHOD_PUT, $uri, self::SUPERVISOR);
        $this->assertResponseStatusCodeSame(403);

        $this->requestApi(Request::METHOD_PUT, $uri, self::STUDENT);
        $this->assertResponseStatusCodeSame(204);

        $unreadEvent = $this->findEventByTitle('unread', $title);
        $readEvent = $this->findEventByTitle('read', $title);
        $allEvent = $this->findEventByTitle('all', $title);

        $this->assertNull($unreadEvent);
        $this->assertIsArray($readEvent);
        $this->assertIsArray($allEvent);

        $viewed = $this->getBool($readEvent, 'viewed');
        $this->assertTrue($viewed);
    }

    public function testViewedNotFound(): void
    {
        $this->requestApi(Request::METHOD_PUT, '/api/key/system-events/999999999/viewed', self::STUDENT);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testViewedAll(): void
    {
        $firstTitle = $this->uniqueName('API system event all');
        $secondTitle = $this->uniqueName('API system event all');
        $this->createWork($firstTitle);
        $this->createWork($secondTitle);

        $this->requestApi(Request::METHOD_PUT, '/api/key/system-events/viewed-all', self::STUDENT);
        $this->assertResponseStatusCodeSame(204);

        $data = $this->requestApi(Request::METHOD_GET, '/api/key/system-events/unread', self::STUDENT);
        $this->assertResponseStatusCodeSame(200);

        $totalCount = $this->getInt($data, 'totalCount');
        $firstEvent = $this->findEventByTitle('read', $firstTitle);
        $secondEvent = $this->findEventByTitle('read', $secondTitle);

        $this->assertSame(0, $totalCount);
        $this->assertIsArray($firstEvent);
        $this->assertIsArray($secondEvent);
    }

    public function testListPagination(): void
    {
        $this->createWork($this->uniqueName('API system event page'));
        $this->createWork($this->uniqueName('API system event page'));

        $firstPage = $this->requestApi(Request::METHOD_GET, '/api/key/system-events/all?limit=1&page=1', self::STUDENT);
        $secondPage = $this->requestApi(Request::METHOD_GET, '/api/key/system-events/all?limit=1&page=2', self::STUDENT);
        $allPage = $this->requestApi(Request::METHOD_GET, '/api/key/system-events/all?limit=2&page=1', self::STUDENT);

        $this->assertResponseStatusCodeSame(200);

        $firstIds = $this->getIds($firstPage, 'result');
        $secondIds = $this->getIds($secondPage, 'result');
        $allIds = $this->getIds($allPage, 'result');
        $count = $this->getInt($firstPage, 'count');
        $totalCount = $this->getInt($firstPage, 'totalCount');

        $this->assertSame(1, $count);
        $this->assertGreaterThanOrEqual(2, $totalCount);
        $this->assertSame($allIds, [...$firstIds, ...$secondIds]);
    }

    public function testListUnknownTypeNotFound(): void
    {
        $this->requestApi(Request::METHOD_GET, '/api/key/system-events/archived', self::STUDENT);

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @return array<mixed>|null
     */
    private function findEventByTitle(string $type, string $title): ?array
    {
        $uri = sprintf('/api/key/system-events/%s?limit=1000', $type);
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::STUDENT);

        $this->assertResponseStatusCodeSame(200);

        $events = $this->getList($data, 'result');

        return array_find($events, fn (array $event): bool => str_contains($this->getString($event, 'title'), $title));
    }
}
