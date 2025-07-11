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

namespace App\Infrastructure\OpenTelemetry\Messenger\Context;

use Symfony\Component\Messenger\Stamp\StampInterface;

readonly class ContextPropagationStamp implements StampInterface
{
    public function __construct(public string $key, public string $value) {}
}
