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

namespace App\Tests\Functional\Api\Domain\Version\Controller\Api;

use App\Domain\Media\Entity\Media;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Tests\Functional\Api\BaseApiTestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

class VersionControllerTest extends BaseApiTestCase
{
    #[DataProvider('provideListCases')]
    public function testList(string $username): void
    {
        $work = $this->createWork($this->uniqueName('API version work'));
        $workId = $this->getInt($work, 'id');
        $uri = sprintf('/api/key/versions/works/%d', $workId);

        $data = $this->requestApi(Request::METHOD_GET, $uri, $username);

        $this->assertResponseStatusCodeSame(200);

        $success = $this->getBool($data, 'success');
        $count = $this->getInt($data, 'count');
        $totalCount = $this->getInt($data, 'totalCount');
        $result = $this->getList($data, 'result');

        $this->assertTrue($success);
        $this->assertSame(0, $count);
        $this->assertSame(0, $totalCount);
        $this->assertSame([], $result);
    }

    public function testListMatchesDatabase(): void
    {
        $supervisorId = $this->getUserId(self::SUPERVISOR);
        $mediaRepository = $this->getEntityManager()->getRepository(Media::class);
        /** @var Media[] $versions */
        $versions = $mediaRepository->findBy(['type' => MediaTypeConstant::WORK_VERSION->value]);

        $version = array_find(
            $versions,
            static fn (Media $media): bool => $media->getWork()?->getSupervisor()?->getId() === $supervisorId
        );

        if ($version === null) {
            $this->markTestSkipped('Test supervisor has no work version.');
        }

        $work = $version->getWork();
        $this->assertNotNull($work);

        /** @var Media[] $workVersions */
        $workVersions = $mediaRepository->findBy([
            'work' => $work,
            'type' => MediaTypeConstant::WORK_VERSION->value
        ]);

        $expectedIds = array_map(static fn (Media $media): int => $media->getId(), $workVersions);
        $uri = sprintf('/api/key/versions/works/%d?limit=1000', $work->getId());
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $totalCount = $this->getInt($data, 'totalCount');
        sort($expectedIds);
        sort($ids);

        $this->assertSame($expectedIds, $ids);
        $this->assertCount($totalCount, $ids);
    }

    public function testListNotFound(): void
    {
        $this->requestApi(Request::METHOD_GET, '/api/key/versions/works/999999999', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(404);
    }

    public static function provideListCases(): Generator
    {
        yield 'supervisor' => [self::SUPERVISOR];
        yield 'author' => [self::STUDENT];
    }
}
