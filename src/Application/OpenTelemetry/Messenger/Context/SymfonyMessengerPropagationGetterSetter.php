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

namespace App\Application\OpenTelemetry\Messenger\Context;

use OpenTelemetry\Context\Propagation\{
    PropagationGetterInterface,
    PropagationSetterInterface
};
use Symfony\Component\Messenger\Envelope;

class SymfonyMessengerPropagationGetterSetter implements PropagationGetterInterface, PropagationSetterInterface
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
     * @param Envelope $carrier
     */
    public function keys($carrier): array
    {
        assert($carrier instanceof Envelope);

        $stamps = $carrier->all(ContextPropagationStamp::class);
        $keys = [];

        foreach ($stamps as $stamp) {
            if ($stamp instanceof ContextPropagationStamp === false) {
                continue;
            }

            $keys[] = $stamp->key;
        }

        return $keys;
    }

    /**
     * @param Envelope $carrier
     */
    public function get($carrier, string $key): ?string
    {
        assert($carrier instanceof Envelope);

        $stamps = $carrier->all(ContextPropagationStamp::class);

        foreach ($stamps as $stamp) {
            if ($stamp instanceof ContextPropagationStamp === false) {
                continue;
            }

            if ($stamp->key === $key) {
                return $stamp->value;
            }
        }

        return null;
    }

    /**
     * @param Envelope $carrier
     */
    public function set(&$carrier, string $key, string $value): void
    {
        assert($carrier instanceof Envelope);

        $carrier = $carrier->with(new ContextPropagationStamp($key, $value));
    }
}
