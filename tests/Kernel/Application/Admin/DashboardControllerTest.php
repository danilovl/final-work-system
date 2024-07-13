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

class DashboardControllerTest extends KernelTestCase
{
    private readonly DashboardController $dashboardController;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->dashboardController = $kernel->getContainer()->get(DashboardController::class);
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

        $this->assertSame(5, count($items));
    }
}
