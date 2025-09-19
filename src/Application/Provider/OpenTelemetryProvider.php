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

namespace App\Application\Provider;

use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;

readonly class OpenTelemetryProvider
{
    public function __construct(private ParameterServiceInterface $parameterService) {}

    public function isEnable(): bool
    {
        return $this->parameterService->getBoolean('open_telemetry.enable');
    }
}
