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

namespace App\Tests\Unit\Domain\Widget\WidgetItem;

use App\Domain\Widget\WidgetItem\BaseWidget;
use App\Infrastructure\Service\TwigRenderService;
use PHPUnit\Framework\TestCase;

class BaseWidgetTest extends TestCase
{
    private BaseWidget $widget;

    protected function setUp(): void
    {
        $twigRenderService = $this->createMock(TwigRenderService::class);
        $twigRenderService->expects($this->any())
            ->method('render')
            ->willReturn('twig');

        $this->widget = new class ( ) extends BaseWidget {};
    }

    public function testSetName(): void
    {
        $this->widget->setName('name');

        $this->assertEquals('name', $this->widget->getName());
    }

    public function testRender(): void
    {
        $this->assertNull($this->widget->render());
    }

    public function testSetParameters(): void
    {
        $this->expectNotToPerformAssertions();

        $this->widget->setParameters(['key' => 'value']);
    }

    public function testHetRenderParameters(): void
    {
        $this->assertEquals([], $this->widget->getRenderParameters());
    }
}
