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

use App\Application\Constant\LocaleConstant;
use App\Application\Service\TwigRenderService;
use App\Domain\Widget\WidgetItem\LocalesWidget;
use PHPUnit\Framework\TestCase;

class LocaleWidgetTest extends TestCase
{
    private LocalesWidget $localesWidget;

    protected function setUp(): void
    {
        $twigRenderService = $this->createMock(TwigRenderService::class);
        $twigRenderService->expects($this->any())
            ->method('render')
            ->willReturn('twig');

        $this->localesWidget = new LocalesWidget(
            $twigRenderService,
            implode('|', LocaleConstant::values())
        );
    }

    public function testGetRenderParameters(): void
    {
        $expected['locales'] = [
            [
                'code' => 'cs',
                'name' => 'čeština'
            ],
            [
                'code' => 'en',
                'name' => 'English'
            ],
            [
                'code' => 'ru',
                'name' => 'русский'
            ]
        ];

        $this->assertEquals($expected, $this->localesWidget->getRenderParameters());
    }

    public function testRender(): void
    {
        $this->assertEquals('twig', $this->localesWidget->render());
    }
}
