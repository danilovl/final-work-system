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

namespace App\Tests\Functional\Api\Domain\WorkCategory\Controller\Api;

use App\Domain\WorkCategory\Entity\WorkCategory;
use App\Tests\Functional\Api\BaseApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class WorkCategoryControllerTest extends BaseApiTestCase
{
    public function testListReturnsOwnCategories(): void
    {
        $supervisorId = $this->getUserId(self::SUPERVISOR);
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/work-categories/', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        /** @var WorkCategory[] $workCategories */
        $workCategories = $this->getEntityManager()
            ->getRepository(WorkCategory::class)
            ->findBy(['owner' => $supervisorId]);

        $expectedIds = array_map(static fn (WorkCategory $workCategory): int => $workCategory->getId(), $workCategories);
        $ids = $this->getIds($data);
        sort($expectedIds);
        sort($ids);

        $this->assertSame($expectedIds, $ids);
    }

    public function testListForbiddenForStudent(): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/work-categories/', self::STUDENT);

        $this->assertResponseStatusCodeSame(403);

        $message = $this->getString($data, 'message');
        $this->assertSame('Access denied', $message);
    }
}
