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

namespace App\Tests\Unit\Infrastructure\Web\Controller;

use App\Infrastructure\Service\TwigRenderService;
use App\Infrastructure\Web\Controller\AwayController;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class AwayControllerTest extends TestCase
{
    #[DataProvider('provideOnlyHttpUrlsArePassedToTemplateCases')]
    public function testOnlyHttpUrlsArePassedToTemplate(string $url, ?string $expectedUrl): void
    {
        $twigRenderService = $this->createMock(TwigRenderService::class);
        $twigRenderService
            ->expects($this->once())
            ->method('renderToResponse')
            ->with('application/away/to.html.twig', ['url' => $expectedUrl])
            ->willReturn(new Response);

        new AwayController($twigRenderService)->to(new Request(['url' => $url]));
    }

    public static function provideOnlyHttpUrlsArePassedToTemplateCases(): Generator
    {
        yield 'https' => ['https://example.com/path?a=1', 'https://example.com/path?a=1'];
        yield 'http uppercase scheme' => ['HTTP://example.com', 'HTTP://example.com'];
        yield 'trimmed' => ['  https://example.com  ', 'https://example.com'];
        yield 'javascript' => ['javascript:alert(1)', null];
        yield 'javascript mixed case' => ['JaVaScRiPt:alert(1)', null];
        yield 'javascript with leading space' => [' javascript:alert(1)', null];
        yield 'data' => ['data:text/html;base64,PHNjcmlwdD4=', null];
        yield 'protocol relative' => ['//evil.com', null];
        yield 'empty' => ['', null];
    }
}
