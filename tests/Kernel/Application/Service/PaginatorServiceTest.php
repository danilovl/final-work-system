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

namespace App\Tests\Kernel\Application\Service;

use App\Application\Service\PaginatorService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class PaginatorServiceTest extends KernelTestCase
{
    private PaginatorService $paginatorService;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->paginatorService = $kernel->getContainer()->get(PaginatorService::class);
    }

    public function testCreatePagination(): void
    {
        $pagination = $this->paginatorService->createPagination(range(0, 100), 1, 10);

        $this->assertSame(1, $pagination->getCurrentPageNumber());
        $this->assertSame(10, $pagination->count());
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
            options: ['pageParameterName' => 'page']);

        $this->assertSame(2, $pagination->getCurrentPageNumber());
        $this->assertSame(20, $pagination->count());
    }
}
