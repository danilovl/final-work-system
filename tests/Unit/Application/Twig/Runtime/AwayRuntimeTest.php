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

use App\Application\Twig\Runtime\AwayRuntime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class AwayRuntimeTest extends TestCase
{
    private readonly AwayRuntime $awayRuntime;

    protected function setUp(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->any())
            ->method('generate')
            ->willReturn('https://final-work.com?to=https://another-web.com');

        $this->awayRuntime = new AwayRuntime($router, 'https://final-work.com');
    }

    public function testEmptyTest(): void
    {
        $text = 'Some text';
        $result = $this->awayRuntime->to($text);

        $this->assertEquals($text, $result);
    }

    public function testReplaceTest(): void
    {
        $text = 'Some text <a href="https://another-web.com">link</a> with <a href="https://another-web.com">link</a>';
        $exceptResult = 'Some text <a href="https://final-work.com?to=https://another-web.com">link</a> with <a href="https://final-work.com?to=https://another-web.com">link</a>';;

        $result = $this->awayRuntime->to($text);

        $this->assertEquals($exceptResult, $result);
    }
}