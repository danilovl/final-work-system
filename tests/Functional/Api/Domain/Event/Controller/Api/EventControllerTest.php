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

namespace App\Tests\Functional\Api\Domain\Event\Controller\Api;

use App\Tests\Functional\Api\BaseApiTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;

class EventControllerTest extends BaseApiTestCase
{
    public function testCreate(): void
    {
        $name = $this->uniqueName('API platform event');
        $addressId = $this->getEventAddressId();
        $event = $this->createEvent($name, $addressId);

        $responseName = $this->getString($event, 'name');
        $ownerId = $this->getInt($event, 'owner', 'id');

        $this->assertSame($name, $responseName);
        $this->assertSame($this->getUserId(self::SUPERVISOR), $ownerId);
    }

    public function testCreateKeepsTimezoneOffset(): void
    {
        $event = $this->createEvent($this->uniqueName('API platform timezone'));
        $eventId = $this->getInt($event, 'id');

        $uri = sprintf('/api/key/events/%d', $eventId);
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);
        $this->assertResponseStatusCodeSame(200);

        $start = $this->getString($data, 'start');
        $end = $this->getString($data, 'end');
        $startDate = new DateTimeImmutable($start);
        $endDate = new DateTimeImmutable($end);
        $expectedStartDate = new DateTimeImmutable('2035-04-02T08:00:00+00:00');
        $expectedEndDate = new DateTimeImmutable('2035-04-02T09:30:00+00:00');

        $this->assertEquals($expectedStartDate, $startDate);
        $this->assertEquals($expectedEndDate, $endDate);
    }

    public function testCreateValidationFailed(): void
    {
        $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api-platform/event/create',
            username: self::SUPERVISOR,
            body: [
                'name' => '',
                'start' => '2035-04-02T08:00:00+00:00',
                'end' => '2035-04-02T09:30:00+00:00',
                'typeId' => 1
            ]
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateUnknownTypeNotFound(): void
    {
        $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api-platform/event/create',
            username: self::SUPERVISOR,
            body: [
                'name' => 'Unknown type',
                'start' => '2035-04-02T08:00:00+00:00',
                'end' => '2035-04-02T09:30:00+00:00',
                'typeId' => 999_999
            ]
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateForbiddenForStudent(): void
    {
        $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api-platform/event/create',
            username: self::STUDENT,
            body: [
                'name' => 'Student event',
                'start' => '2035-04-02T08:00:00+00:00',
                'end' => '2035-04-02T09:30:00+00:00',
                'typeId' => 1
            ]
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testListOwner(): void
    {
        $event = $this->createEvent($this->uniqueName('API platform list'));
        $eventId = $this->getInt($event, 'id');

        $data = $this->requestApi(Request::METHOD_GET, '/api-platform/event/list-owner?limit=1000', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $totalCount = $this->getInt($data, 'totalCount');
        $currentItemCount = $this->getInt($data, 'currentItemCount');

        $this->assertContains($eventId, $ids);
        $this->assertCount($currentItemCount, $ids);
        $this->assertGreaterThanOrEqual($currentItemCount, $totalCount);

        $data = $this->requestApi(Request::METHOD_GET, '/api-platform/event/list-owner?limit=1000', self::STUDENT);
        $ids = $this->getIds($data, 'result');
        $this->assertNotContains($eventId, $ids);
    }

    public function testDetail(): void
    {
        $name = $this->uniqueName('API event detail');
        $event = $this->createEvent($name);
        $eventId = $this->getInt($event, 'id');
        $uri = sprintf('/api/key/events/%d', $eventId);

        $data = $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $id = $this->getInt($data, 'id');
        $responseName = $this->getString($data, 'name');
        $typeId = $this->getInt($data, 'type', 'id');

        $this->assertSame($eventId, $id);
        $this->assertSame($name, $responseName);
        $this->assertSame(1, $typeId);
    }

    public function testDetailNotFound(): void
    {
        $this->requestApi(Request::METHOD_GET, '/api/key/events/999999999', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testListWork(): void
    {
        $work = $this->createWork($this->uniqueName('API event work'));
        $workId = $this->getInt($work, 'id');
        $uri = sprintf('/api/key/events/works/%d', $workId);

        $data = $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $success = $this->getBool($data, 'success');
        $totalCount = $this->getInt($data, 'totalCount');
        $result = $this->getList($data, 'result');

        $this->assertTrue($success);
        $this->assertSame(0, $totalCount);
        $this->assertSame([], $result);
    }

    public function testListWorkNotFound(): void
    {
        $this->requestApi(Request::METHOD_GET, '/api/key/events/works/999999999', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete(): void
    {
        $event = $this->createEvent($this->uniqueName('API event delete'));
        $eventId = $this->getInt($event, 'id');
        $uri = sprintf('/api/key/events/%d', $eventId);

        $this->requestApi(Request::METHOD_DELETE, $uri, self::STUDENT);
        $this->assertResponseStatusCodeSame(403);

        $this->requestApi(Request::METHOD_DELETE, $uri, self::SUPERVISOR);
        $this->assertResponseStatusCodeSame(204);

        $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @return array<mixed>
     */
    private function createEvent(string $name, ?int $addressId = null): array
    {
        $data = $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api-platform/event/create',
            username: self::SUPERVISOR,
            body: [
                'name' => $name,
                'start' => '2035-04-02T08:00:00+00:00',
                'end' => '2035-04-02T09:30:00+00:00',
                'typeId' => 1,
                'addressId' => $addressId
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->createdEventIds[] = $this->getInt($data, 'id');

        return $data;
    }
}
