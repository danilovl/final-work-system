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

namespace App\Tests\Functional\Api\Domain\WorkType\Controller\Api;

use App\Domain\WorkType\Entity\WorkType;
use App\Tests\Functional\Api\BaseApiTestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

class WorkTypeControllerTest extends BaseApiTestCase
{
    #[DataProvider('provideListCases')]
    public function testList(string $username): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/work-types/', $username);

        $this->assertResponseStatusCodeSame(200);

        /** @var WorkType[] $workTypes */
        $workTypes = $this->getEntityManager()
            ->getRepository(WorkType::class)
            ->findAll();

        $expectedIds = array_map(static fn (WorkType $workType): int => $workType->getId(), $workTypes);
        $ids = $this->getIds($data);
        sort($expectedIds);
        sort($ids);

        $this->assertNotEmpty($ids);
        $this->assertSame($expectedIds, $ids);

        foreach ($this->getList($data) as $workType) {
            $name = $this->getString($workType, 'name');
            $shortcut = $this->getString($workType, 'shortcut');

            $this->assertNotSame('', $name);
            $this->assertNotSame('', $shortcut);
        }
    }

    public static function provideListCases(): Generator
    {
        yield 'supervisor' => [self::SUPERVISOR];
        yield 'student' => [self::STUDENT];
    }
}
