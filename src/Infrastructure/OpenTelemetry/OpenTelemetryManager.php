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

namespace App\Infrastructure\OpenTelemetry;

use App\Application\Exception\InvalidArgumentException;

class OpenTelemetryManager
{
    /**
     * @var string[]
     */
    private array $openTelemetryRegistrations = [];

    public function addRegistration(string $name): void
    {
        if (in_array($name, $this->openTelemetryRegistrations, true)) {
            throw new InvalidArgumentException(sprintf('OpenTelemetry registration "%s" is already registered', $name));
        }

        $this->openTelemetryRegistrations[] = $name;
    }

    /**
     * @return string[]
     */
    public function getOpenTelemetryRegistrations(): array
    {
        return $this->openTelemetryRegistrations;
    }
}
