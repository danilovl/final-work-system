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

namespace App\Tests\Unit\Application\Util;

use App\Application\Util\StreamedResponseUtil;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamedResponseUtilTest extends TestCase
{
    #[DataProvider('responseProvider')]
    public function testCreateStreamedResponse(callable $callback, StreamedResponse $expectedResponse): void
    {
        $response = StreamedResponseUtil::create($callback);
        /** @var DateTimeImmutable $date */
        $date = $expectedResponse->getDate();
        $response->setDate($date);

        $this->assertEquals($expectedResponse, $response);
    }

    public static function responseProvider(): Generator
    {
        $streamedResponse = new StreamedResponse;

        $streamedResponse->headers->add([
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
            'Cache-Control' => 'no-cache',
        ]);

        yield [
            static fn (): string => 'This is a test response.',
            $streamedResponse->setCallback(static fn (): string => 'This is a test response.'),
        ];

        yield [
            static fn (): int => 0,
            $streamedResponse->setCallback(static fn (): int => 0),
        ];
    }
}
