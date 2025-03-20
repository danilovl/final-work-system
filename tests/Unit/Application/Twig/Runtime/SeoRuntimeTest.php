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
    private SeoPageService $seoPageService;

    private SeoRuntime $seoRuntime;

    protected function setUp(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturn('trans');

        $this->seoPageService = new SeoPageService($translator);
        $this->seoPageService->addMeta('charset', 'utf-8', 'content');
        $this->seoPageService->addMeta('name', 'viewport', 'content');
        $this->seoPageService->addMeta('meta', 'viewport', '');

        $this->seoRuntime = new SeoRuntime($this->seoPageService);
    }

    public function testSetTitle(): void
    {
        $this->seoRuntime->setTitle('test title');

        $this->assertEquals('<title>test title</title>', $this->seoRuntime->title());
    }

    public function testGetDataNull(): void
    {
        $this->seoPageService->setTitle(null);

        $this->assertNull($this->seoRuntime->title());
    }

    public function testGetMetaData(): void
    {
        $result = '<meta name="viewport" content="content" />' . "\n";
        $result .= '<meta charset="utf-8" content="content" />' . "\n";
        $result .= '<meta meta="viewport" />' . "\n";

        $this->assertEquals($result, $this->seoRuntime->getMetaData());
    }
}
