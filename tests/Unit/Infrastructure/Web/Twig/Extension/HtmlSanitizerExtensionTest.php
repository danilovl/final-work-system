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

namespace App\Tests\Unit\Infrastructure\Web\Twig\Extension;

use App\Infrastructure\Web\Twig\Extension\HtmlSanitizerExtension;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\HtmlSanitizerExtension as BaseHtmlSanitizerExtension;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HtmlSanitizer\{
    HtmlSanitizer,
    HtmlSanitizerConfig
};

class HtmlSanitizerExtensionTest extends TestCase
{
    private HtmlSanitizerExtension $extension;

    protected function setUp(): void
    {
        $config = new HtmlSanitizerConfig()->allowSafeElements();
        $sanitizers = new ServiceLocator([
            'default' => static fn (): HtmlSanitizer => new HtmlSanitizer($config)
        ]);

        $this->extension = new HtmlSanitizerExtension(new BaseHtmlSanitizerExtension($sanitizers));
    }

    public function testFilterName(): void
    {
        $filters = $this->extension->getFilters();

        $this->assertCount(1, $filters);
        $this->assertSame('sanitize_html', $filters[0]->getName());
    }

    #[DataProvider('provideSanitizeCases')]
    public function testSanitize(?string $html, string $expected): void
    {
        $result = $this->extension->sanitize($html);

        $this->assertSame($expected, $result);
    }

    public static function provideSanitizeCases(): Generator
    {
        yield 'null' => [
            'html' => null,
            'expected' => ''
        ];

        yield 'empty string' => [
            'html' => '',
            'expected' => ''
        ];

        yield 'safe html is kept' => [
            'html' => '<p>Dobrý den</p>',
            'expected' => '<p>Dobrý den</p>'
        ];

        yield 'script is removed' => [
            'html' => '<p>text</p><script>alert(1)</script>',
            'expected' => '<p>text</p>'
        ];
    }
}
