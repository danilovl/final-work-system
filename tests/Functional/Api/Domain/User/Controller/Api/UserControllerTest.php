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

namespace App\Tests\Functional\Api\Domain\User\Controller\Api;

use App\Tests\Functional\Api\BaseApiTestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

class UserControllerTest extends BaseApiTestCase
{
    #[DataProvider('provideDetailCases')]
    public function testDetail(string $username, string $role): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/users/detail', $username);

        $this->assertResponseStatusCodeSame(200);

        $responseUsername = $this->getString($data, 'username');
        $roles = $this->getArray($data, 'roles');
        $headers = $this->getAuthHeaders($username);
        $token = $this->getString($data, 'token');

        $this->assertSame($username, $responseUsername);
        $this->assertContains($role, $roles);
        $this->assertContains($token, $headers);
        $this->assertArrayNotHasKey('password', $data);
    }

    #[DataProvider('provideListCases')]
    public function testList(string $type): void
    {
        $uri = sprintf('/api/key/users/%s?limit=2', $type);
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $numItemsPerPage = $this->getInt($data, 'numItemsPerPage');
        $totalCount = $this->getInt($data, 'totalCount');
        $currentItemCount = $this->getInt($data, 'currentItemCount');
        $result = $this->getList($data, 'result');

        $this->assertSame(2, $numItemsPerPage);
        $this->assertLessThanOrEqual(2, $currentItemCount);
        $this->assertCount($currentItemCount, $result);
        $this->assertGreaterThanOrEqual($currentItemCount, $totalCount);

        foreach ($result as $item) {
            $userId = $this->getInt($item, 'user', 'id');
            $this->getList($item, 'works');

            $this->assertGreaterThan(0, $userId);
        }
    }

    public function testListAuthorContainsWorkAuthor(): void
    {
        $title = $this->uniqueName('API user list work');
        $work = $this->createWork($title);
        $workId = $this->getInt($work, 'id');
        $studentId = $this->getUserId(self::STUDENT);

        $data = $this->requestApi(Request::METHOD_GET, '/api/key/users/author?limit=1000', self::SUPERVISOR);
        $this->assertResponseStatusCodeSame(200);

        $result = $this->getList($data, 'result');
        $studentItem = array_find($result, fn (array $item): bool => $this->getInt($item, 'user', 'id') === $studentId);
        $this->assertIsArray($studentItem);

        $workIds = $this->getIds($studentItem, 'works');
        $this->assertContains($workId, $workIds);
    }

    public function testListPagination(): void
    {
        $firstPage = $this->requestApi(Request::METHOD_GET, '/api/key/users/author?limit=1&page=1', self::SUPERVISOR);
        $secondPage = $this->requestApi(Request::METHOD_GET, '/api/key/users/author?limit=1&page=2', self::SUPERVISOR);
        $allPage = $this->requestApi(Request::METHOD_GET, '/api/key/users/author?limit=2&page=1', self::SUPERVISOR);

        $totalCount = $this->getInt($allPage, 'totalCount');
        if ($totalCount < 2) {
            $this->markTestSkipped('Not enough authors for pagination.');
        }

        $firstId = $this->getInt($firstPage, 'result', 0, 'user', 'id');
        $secondId = $this->getInt($secondPage, 'result', 0, 'user', 'id');
        $allIds = [
            $this->getInt($allPage, 'result', 0, 'user', 'id'),
            $this->getInt($allPage, 'result', 1, 'user', 'id')
        ];

        $this->assertSame($allIds, [$firstId, $secondId]);
    }

    public function testListForbiddenForStudent(): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/users/author', self::STUDENT);

        $this->assertResponseStatusCodeSame(403);

        $message = $this->getString($data, 'message');
        $this->assertSame('Access denied', $message);
    }

    public function testListUnknownTypeNotFound(): void
    {
        $this->requestApi(Request::METHOD_GET, '/api/key/users/supervisor', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(404);
    }

    #[DataProvider('provideSearchCases')]
    public function testSearch(string $uri): void
    {
        $studentId = $this->getUserId(self::STUDENT);
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $this->assertContains($studentId, $ids);

        foreach ($this->getList($data, 'result') as $user) {
            $roles = $this->getArray($user, 'roles');
            $this->assertContains('ROLE_STUDENT', $roles);
        }
    }

    public function testSearchNothingFound(): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/users/author/search?search=zzz-no-user-zzz', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $totalCount = $this->getInt($data, 'totalCount');
        $result = $this->getList($data, 'result');

        $this->assertSame(0, $totalCount);
        $this->assertSame([], $result);
    }

    public function testSearchForbiddenForStudent(): void
    {
        $this->requestApi(Request::METHOD_GET, '/api/key/users/author/search?search=student', self::STUDENT);

        $this->assertResponseStatusCodeSame(403);
    }

    public static function provideDetailCases(): Generator
    {
        yield 'supervisor' => [self::SUPERVISOR, 'ROLE_SUPERVISOR'];
        yield 'student' => [self::STUDENT, 'ROLE_STUDENT'];
    }

    public static function provideListCases(): Generator
    {
        yield 'author' => ['author'];
        yield 'opponent' => ['opponent'];
        yield 'consultant' => ['consultant'];
        yield 'unused' => ['unused'];
    }

    public static function provideSearchCases(): Generator
    {
        yield 'search by type' => ['/api/key/users/search/author?search=student'];
        yield 'type search' => ['/api/key/users/author/search?search=student&limit=5'];
        yield 'query parameter' => ['/api/key/users/author/search?query=STUDENT'];
        yield 'term parameter' => ['/api/key/users/author/search?term=stud'];
    }
}
