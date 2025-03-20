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

namespace App\Tests\Kernel\Application\Admin;

use App\Application\Admin\DashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DashboardControllerTest extends KernelTestCase
{
    private DashboardController $dashboardController;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->dashboardController = $kernel->getContainer()->get(DashboardController::class);
    }

    public function testIndex(): void
    {
        $this->dashboardController->adminUrlGenerator->setDashboard(DashboardController::class);

        $response = $this->dashboardController->index();

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $isContains = str_contains(
            $response->getTargetUrl(),
            'admin/dashboard?crudAction=index&crudControllerFqcn=App%5CDomain%5CUser%5CAdmin%5CUserCrudController'
        );
        $this->assertTrue($isContains);
    }

    public function testConfigureDashboard(): void
    {
        $dashboard = $this->dashboardController->configureDashboard();

        $this->assertInstanceOf(Dashboard::class, $dashboard);
        $this->assertSame('Final Work System', $dashboard->getAsDto()->getTitle());
    }

    public function testConfigureMenuItems(): void
    {
        $items = $this->dashboardController->configureMenuItems();
        $items = iterator_to_array($items);

        $this->assertCount(5, $items);
    }
}
