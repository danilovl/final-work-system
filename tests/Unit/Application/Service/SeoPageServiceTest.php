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

namespace App\Tests\Unit\Application\Service;

use App\Application\Exception\RuntimeException;
use App\Application\Service\SeoPageService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class SeoPageServiceTest extends TestCase
{
    private readonly SeoPageService $seoPageService;

    public function setUp(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturn('trans');

        $this->seoPageService = new SeoPageService($translator);
    }

    public function testSetTitle(): void
    {
        $this->seoPageService->setTitle('title');

        $this->assertSame('title', $this->seoPageService->getTitle());
    }

    public function testAddTitle(): void
    {
        $this->seoPageService->setTitle('title');
        $this->seoPageService->addTitle('title', 'separator');

        $this->assertSame('titleseparatortitle', $this->seoPageService->getTitle());
    }

    public function testGetTransTitle(): void
    {
        $result = $this->seoPageService->getTransTitle('title');
        $this->assertSame('title', $result);

        $result = $this->seoPageService->getTransTitle('app.title');
        $this->assertSame('trans', $result);
    }

    public function testGetMetas(): void
    {
        $metas = [
            'http-equiv' => [],
            'name' => [],
            'schema' => [],
            'charset' => [],
            'property' => [],
        ];

        $this->assertEquals($metas, $this->seoPageService->getMetas());
    }

    public function testHasMeta(): void
    {
        $this->assertFalse($this->seoPageService->hasMeta('http-equiv', 'test'));

        $this->seoPageService->addMeta('http-equiv', 'test', 'content');
        $this->assertTrue($this->seoPageService->hasMeta('http-equiv', 'test'));
    }

    public function testSetMetas(): void
    {
        $this->seoPageService->setMetas([
            'name' => ['name' => 'meta']
        ]);

        $result = [
            'name' => ['name' => ['meta', []]]
        ];

        $this->assertEquals($result, $this->seoPageService->getMetas());

        $this->expectException(RuntimeException::class);

        $this->seoPageService->setMetas(['name' => 'string']);
    }
}
