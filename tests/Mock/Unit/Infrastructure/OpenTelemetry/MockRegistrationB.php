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

namespace App\Tests\Mock\Unit\Infrastructure\OpenTelemetry;

use App\Infrastructure\OpenTelemetry\OpenTelemetryRegistrationInterface;

class MockRegistrationB implements OpenTelemetryRegistrationInterface
{
    public static int $called = 0;

    public static function registration(): void
    {
        self::$called++;
    }
}
