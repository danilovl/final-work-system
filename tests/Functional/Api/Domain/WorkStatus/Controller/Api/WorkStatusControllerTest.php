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

namespace App\Tests\Functional\Api\Domain\WorkStatus\Controller\Api;

use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Tests\Functional\Api\BaseApiTestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

class WorkStatusControllerTest extends BaseApiTestCase
{
    #[DataProvider('provideListCases')]
    public function testList(string $username): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/work-statuses/', $username);

        $this->assertResponseStatusCodeSame(200);

        /** @var WorkStatus[] $workStatuses */
        $workStatuses = $this->getEntityManager()
            ->getRepository(WorkStatus::class)
            ->findAll();

        $expectedIds = array_map(static fn (WorkStatus $workStatus): int => $workStatus->getId(), $workStatuses);
        $ids = $this->getIds($data);
        sort($expectedIds);
        sort($ids);

        $this->assertNotEmpty($ids);
        $this->assertSame($expectedIds, $ids);

        foreach ($this->getList($data) as $workStatus) {
            $name = $this->getString($workStatus, 'name');
            $color = $this->getString($workStatus, 'color');

            $this->assertNotSame('', $name);
            $this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', $color);
        }
    }

    public static function provideListCases(): Generator
    {
        yield 'supervisor' => [self::SUPERVISOR];
        yield 'student' => [self::STUDENT];
    }
}
