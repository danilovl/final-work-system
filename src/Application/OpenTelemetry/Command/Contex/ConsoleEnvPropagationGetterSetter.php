<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\OpenTelemetry\Command\Contex;

use OpenTelemetry\Context\Propagation\{
    PropagationGetterInterface,
    PropagationSetterInterface
};

class ConsoleEnvPropagationGetterSetter implements PropagationGetterInterface, PropagationSetterInterface
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param mixed $carrier
     */
    public function keys($carrier): array
    {
        if (is_array($carrier) === false) {
            return [];
        }

        return $carrier;
    }

    /**
     * @param mixed $carrier
     */
    public function get($carrier, string $key): ?string
    {
        if (is_array($carrier) === false) {
            return null;
        }

        return $carrier[$key] ?? null;
    }

    /**
     * @param mixed $carrier
     */
    public function set(&$carrier, string $key, string $value): void
    {
        putenv(sprintf('%s=%s', $key, $value));
    }
}
