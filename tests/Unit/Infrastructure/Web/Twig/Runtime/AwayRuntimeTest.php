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

namespace App\Tests\Unit\Infrastructure\Web\Twig\Runtime;

use App\Infrastructure\Web\Twig\Runtime\AwayRuntime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class AwayRuntimeTest extends TestCase
{
    private AwayRuntime $awayRuntime;

    protected function setUp(): void
    {
        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')
            ->willReturn('https://final-work.com?to=https://another-web.com');

        $this->awayRuntime = new AwayRuntime($router, 'final-work.com');
    }

    public function testNull(): void
    {
        $text = null;
        $result = $this->awayRuntime->to($text);

        $this->assertNull($result);
    }

    public function testEmptyTest(): void
    {
        $text = 'Some text';
        $result = $this->awayRuntime->to($text);

        $this->assertEquals($text, $result);
    }

    public function testReplaceTest(): void
    {
        $text = 'Some text <a href="https://another-web.com">link</a> with <a href="https://another-web.com">link</a><a href="https://final-work.com">link</a>';
        $exceptResult = 'Some text <a href="https://final-work.com?to=https://another-web.com">link</a> with <a href="https://final-work.com?to=https://another-web.com">link</a><a href="https://final-work.com">link</a>';

        $result = $this->awayRuntime->to($text);

        $this->assertEquals($exceptResult, $result);
    }
}
