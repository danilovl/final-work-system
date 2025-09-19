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

namespace App\Application\OpenTelemetry\Guzzle\Contex;

use OpenTelemetry\Context\Propagation\{
    PropagationGetterInterface,
    PropagationSetterInterface
};
use Psr\Http\Message\RequestInterface;

class PsrHeadersPropagationSetter implements PropagationGetterInterface, PropagationSetterInterface
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
     * @param RequestInterface $carrier
     */
    public function keys($carrier): array
    {
        assert($carrier instanceof RequestInterface);

        return array_keys($carrier->getHeaders());
    }

    /**
     * @param RequestInterface $carrier
     */
    public function get($carrier, string $key): ?string
    {
        assert($carrier instanceof RequestInterface);

        return $carrier->getHeader($key)[0] ?? null;
    }

    /**
     * @param RequestInterface $carrier
     */
    public function set(&$carrier, string $key, string $value): void
    {
        assert($carrier instanceof RequestInterface);

        $carrier = $carrier->withHeader($key, $value);
    }
}
