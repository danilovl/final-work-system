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

namespace App\Tests\Functional\Api\Domain\EventCalendar\Controller\Api;

use App\Tests\Functional\Api\BaseApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class EventCalendarControllerTest extends BaseApiTestCase
{
    public function testManageCreateData(): void
    {
        $addressId = $this->getEventAddressId();
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/events/calendar/manage/create/data', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $types = $this->getList($data, 'types');
        $addresses = $this->getList($data, 'addresses');

        $this->assertNotEmpty($types);

        foreach ($types as $type) {
            $name = $this->getString($type, 'name');
            $this->getBool($type, 'registrable');

            $this->assertNotSame('', $name);
        }

        $addressIds = $this->getIds($data, 'addresses');
        $this->assertContains($addressId, $addressIds);
        $this->getList($data, 'participants');
    }

    public function testManageCreateDataForbiddenForStudent(): void
    {
        $this->requestApi(Request::METHOD_GET, '/api/key/events/calendar/manage/create/data', self::STUDENT);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreateWithParticipant(): void
    {
        $work = $this->createWork($this->uniqueName('API calendar work'));
        $workId = $this->getInt($work, 'id');
        $name = $this->uniqueName('API calendar event');

        $event = $this->createEvent(
            name: $name,
            start: '2035-03-05 10:00:00',
            end: '2035-03-05 11:00:00',
            userId: $this->getUserId(self::STUDENT),
            workId: $workId
        );

        $title = $this->getString($event, 'title');
        $start = $this->getString($event, 'start');
        $end = $this->getString($event, 'end');

        $this->assertStringContainsString($name, $title);
        $this->assertSame('2035-03-05 10:00:00', $start);
        $this->assertSame('2035-03-05 11:00:00', $end);

        $eventId = $this->getInt($event, 'id');
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/events/calendar/manage?start=2035-03-01&end=2035-03-10', self::SUPERVISOR);
        $this->assertResponseStatusCodeSame(200);

        $calendarEvent = array_find($this->getList($data), fn (array $item): bool => $this->getInt($item, 'id') === $eventId);
        $this->assertIsArray($calendarEvent);

        $hasParticipant = $this->getBool($calendarEvent, 'hasParticipant');
        $this->assertTrue($hasParticipant);

        $uri = sprintf('/api/key/events/works/%d', $workId);
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::STUDENT);
        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $this->assertSame([$eventId], $ids);
    }

    public function testCreateValidationFailed(): void
    {
        $data = $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/key/events/calendar/manage/create',
            username: self::SUPERVISOR,
            body: [
                'typeId' => 1,
                'name' => 'Invalid',
                'addressId' => $this->getEventAddressId(),
                'userId' => null,
                'workId' => null,
                'start' => '',
                'end' => 'not-a-date'
            ]
        );

        $this->assertResponseStatusCodeSame(400);

        $errors = $this->getList($data, 'errors');
        $properties = array_map(fn (array $error): string => $this->getString($error, 'property'), $errors);

        $this->assertContains('start', $properties);
        $this->assertContains('end', $properties);
    }

    public function testCreateUnknownTypeNotFound(): void
    {
        $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/key/events/calendar/manage/create',
            username: self::SUPERVISOR,
            body: [
                'typeId' => 999_999,
                'name' => 'Unknown type',
                'addressId' => $this->getEventAddressId(),
                'userId' => null,
                'workId' => null,
                'start' => '2035-03-07 10:00:00',
                'end' => '2035-03-07 11:00:00'
            ]
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateForbiddenForStudent(): void
    {
        $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/key/events/calendar/manage/create',
            username: self::STUDENT,
            body: [
                'typeId' => 1,
                'name' => 'Student event',
                'addressId' => 1,
                'userId' => null,
                'workId' => null,
                'start' => '2035-03-07 10:00:00',
                'end' => '2035-03-07 11:00:00'
            ]
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testReservation(): void
    {
        $work = $this->createWork($this->uniqueName('API reservation work'));
        $workId = $this->getInt($work, 'id');
        $event = $this->createEvent(
            name: $this->uniqueName('API reservation event'),
            start: '2035-03-08 10:00:00',
            end: '2035-03-08 11:00:00'
        );
        $eventId = $this->getInt($event, 'id');

        $data = $this->requestApi(Request::METHOD_GET, '/api/key/events/calendar/reservation/user/works', self::STUDENT);
        $this->assertResponseStatusCodeSame(200);

        $workIds = $this->getIds($data);
        $this->assertContains($workId, $workIds);

        $data = $this->requestApi(Request::METHOD_GET, '/api/key/events/calendar/reservation?start=2035-03-01&end=2035-03-10', self::STUDENT);
        $this->assertResponseStatusCodeSame(200);

        $calendarEvent = array_find($this->getList($data), fn (array $item): bool => $this->getInt($item, 'id') === $eventId);
        $this->assertIsArray($calendarEvent);

        $hasParticipant = $this->getBool($calendarEvent, 'hasParticipant');
        $this->assertFalse($hasParticipant);

        $uri = sprintf('/api/key/events/calendar/reservation/events/%d/works/%d', $eventId, $workId);
        $this->requestApi(Request::METHOD_POST, $uri, self::STUDENT);
        $this->assertResponseStatusCodeSame(204);

        $data = $this->requestApi(Request::METHOD_GET, '/api/key/events/calendar/reservation?start=2035-03-01&end=2035-03-10', self::STUDENT);
        $calendarEvent = array_find($this->getList($data), fn (array $item): bool => $this->getInt($item, 'id') === $eventId);
        $this->assertIsArray($calendarEvent);

        $hasParticipant = $this->getBool($calendarEvent, 'hasParticipant');
        $this->assertTrue($hasParticipant);

        $this->requestApi(Request::METHOD_POST, $uri, self::STUDENT);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testReservationForbiddenForSupervisor(): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/events/calendar/reservation/user/works', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(403);

        $message = $this->getString($data, 'message');
        $this->assertSame('Access denied', $message);
    }

    public function testGetEventValidationFailed(): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/events/calendar/manage', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(400);

        $errors = $this->getList($data, 'errors');
        $this->assertCount(2, $errors);
    }

    public function testGetEventUnknownTypeNotFound(): void
    {
        $this->requestApi(Request::METHOD_GET, '/api/key/events/calendar/detail?start=2035-03-01&end=2035-03-10', self::STUDENT);

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @return array<mixed>
     */
    private function createEvent(
        string $name,
        string $start,
        string $end,
        ?int $userId = null,
        ?int $workId = null
    ): array {
        $data = $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/key/events/calendar/manage/create',
            username: self::SUPERVISOR,
            body: [
                'typeId' => 1,
                'name' => $name,
                'addressId' => $this->getEventAddressId(),
                'userId' => $userId,
                'workId' => $workId,
                'start' => $start,
                'end' => $end
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->createdEventIds[] = $this->getInt($data, 'id');

        return $data;
    }
}
