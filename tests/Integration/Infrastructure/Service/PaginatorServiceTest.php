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

namespace App\Tests\Integration\Infrastructure\Service;

use App\Domain\User\Entity\User;
use App\Infrastructure\Service\PaginatorService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class PaginatorServiceTest extends KernelTestCase
{
    private PaginatorService $paginatorService;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->paginatorService = $kernel->getContainer()->get(PaginatorService::class);
    }

    public function testCreatePagination(): void
    {
        $pagination = $this->paginatorService->createPagination(
            target: range(0, 100),
            page: 1,
            limit: 10
        );

        $this->assertSame(1, $pagination->getCurrentPageNumber());
        $this->assertSame(10, $pagination->count());

        $this->paginatorService->createPagination(
            target: [new User],
            page: 1,
            limit: 10,
            detachEntity: true
        );
    }

    public function testCreatePaginationRequest(): void
    {
        $request = new Request(query: [
            'page' => 2,
            'limit' => 20
        ]);

        $pagination = $this->paginatorService->createPaginationRequest(
            request: $request,
            target: range(0, 100),
            options: ['pageParameterName' => 'page']
        );

        $this->assertSame(2, $pagination->getCurrentPageNumber());
        $this->assertSame(20, $pagination->count());

        $this->paginatorService->createPaginationRequest(
            request: $request,
            target: [new User],
            options: ['pageParameterName' => 'page'],
            detachEntity: true
        );
    }
}
