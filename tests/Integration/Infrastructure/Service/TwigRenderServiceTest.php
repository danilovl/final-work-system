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

namespace App\Tests\Integration\Infrastructure\Service;

use App\Infrastructure\Service\TwigRenderService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\{
    Request,
    Response};

class TwigRenderServiceTest extends KernelTestCase
{
    private TwigRenderService $twigRenderService;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->twigRenderService = $kernel->getContainer()->get(TwigRenderService::class);
    }

    public function testRender(): void
    {
        $content = $this->twigRenderService->render('simple.html.twig', ['first' => 'first', 'second' => 'second']);

        $this->assertStringContainsString('first', $content);
        $this->assertStringContainsString('second', $content);
    }

    public function testRenderToResponse(): void
    {
        $response = $this->twigRenderService->renderToResponse('simple.html.twig', ['first' => 'first', 'second' => 'second']);

        $this->assertInstanceOf(Response::class, $response);

        /** @var string $content */
        $content = $response->getContent();
        $this->assertStringContainsString('first', $content);
        $this->assertStringContainsString('second', $content);
    }

    public function testAjaxOrNormalFolder(): void
    {
        $request = new Request;
        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'simple.html.twig');

        $this->assertSame('simple.html.twig', $template);

        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'simple.html.twig');
        $this->assertSame('ajax/simple.html.twig', $template);
    }

    public function testGetLoader(): void
    {
        $this->expectNotToPerformAssertions();

        $this->twigRenderService->getLoader();
    }
}
