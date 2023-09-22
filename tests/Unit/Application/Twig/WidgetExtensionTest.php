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

namespace App\Tests\Unit\Application\Twig;

use App\Application\Twig\WidgetExtension;
use App\Domain\Widget\Service\WidgetManagerService;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

class WidgetExtensionTest extends TestCase
{
    public function testGetFunctions(): void
    {
        $widgetManagerService = $this->createMock(WidgetManagerService::class);

        $seoExtension = new WidgetExtension($widgetManagerService);
        $twigFunction = array_map(static function (TwigFunction $twigFunction): string {
            return $twigFunction->getName();
        }, $seoExtension->getFunctions());

        $this->assertEquals(
            ['widget', 'widget_group', 'widget_homepage_notify'],
            $twigFunction
        );
    }
}
