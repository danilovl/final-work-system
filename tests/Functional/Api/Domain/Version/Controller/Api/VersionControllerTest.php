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
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Tests\Functional\Api\BaseApiTestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class VersionControllerTest extends BaseApiTestCase
{
    private const string VERSION_FILE = __DIR__ . '/../../../../../../Mock/image/test_png.png';

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

    public function testListContainsUploadedVersions(): void
    {
        $work = $this->createWork($this->uniqueName('API version upload work'));
        $workId = $this->getInt($work, 'id');
        $firstVersionId = $this->createVersion($workId, 'API version first');
        $secondVersionId = $this->createVersion($workId, 'API version second');

        $uri = sprintf('/api/key/versions/works/%d', $workId);
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::STUDENT);

        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $count = $this->getInt($data, 'count');
        $totalCount = $this->getInt($data, 'totalCount');
        $expectedIds = [$firstVersionId, $secondVersionId];
        sort($ids);
        sort($expectedIds);

        $this->assertSame($expectedIds, $ids);
        $this->assertSame(2, $count);
        $this->assertSame(2, $totalCount);
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

    private function createVersion(int $workId, string $name): int
    {
        $supervisorId = $this->getUserId(self::SUPERVISOR);
        $entityManager = $this->getEntityManager();

        $work = $entityManager->find(Work::class, $workId);
        $owner = $entityManager->find(User::class, $supervisorId);
        $type = $entityManager->find(MediaType::class, MediaTypeConstant::WORK_VERSION->value);
        $this->assertNotNull($work);
        $this->assertNotNull($owner);
        $this->assertNotNull($type);

        $filePath = tempnam(sys_get_temp_dir(), 'api-version');
        $this->assertIsString($filePath);
        copy(self::VERSION_FILE, $filePath);

        $uploadedFile = new UploadedFile(
            path: $filePath,
            originalName: 'version.png',
            mimeType: 'image/png',
            test: true
        );

        $version = new Media;
        $version->setName($name);
        $version->setOwner($owner);
        $version->setType($type);
        $version->setWork($work);
        $version->setActive(true);
        $version->setUploadMedia($uploadedFile);

        $entityManager->persist($version);
        $entityManager->flush();
        unlink($filePath);

        return $version->getId();
    }
}
