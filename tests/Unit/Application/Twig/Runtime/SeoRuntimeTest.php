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

namespace App\Tests\Unit\Application\Twig\Runtime;

use App\Application\Service\SeoPageService;
use App\Application\Twig\Runtime\SeoRuntime;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class SeoRuntimeTest extends TestCase
{
    private readonly SeoRuntime $seoRuntime;

    protected function setUp(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturn('trans');

        $seoPageService = new SeoPageService($translator);
        $seoPageService->addMeta('charset', 'utf-8', 'content');
        $seoPageService->addMeta('name', 'viewport', 'content');

        $this->seoRuntime = new SeoRuntime($seoPageService);
    }

    public function testSetTitle(): void
    {
        $this->seoRuntime->setTitle('test title');

        $this->assertEquals('<title>test title</title>', $this->seoRuntime->getTitle());
    }

    public function testGetMetaData(): void
    {
        $result = '<meta name="viewport" content="content" />' . "\n";
        $result .= '<meta charset="utf-8" content="content" />' . "\n";

        $this->assertEquals($result, $this->seoRuntime->getMetaData());
    }
}
