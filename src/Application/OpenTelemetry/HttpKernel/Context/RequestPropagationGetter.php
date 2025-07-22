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

namespace App\Application\OpenTelemetry\HttpKernel\Context;

use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestPropagationGetter implements PropagationGetterInterface
{
    public static function instance(): self
    {
        static $instance;

        return $instance ??= new self();
    }

    public function keys($carrier): array
    {
        assert($carrier instanceof Request);

        return $carrier->headers->keys();
    }

    public function get($carrier, string $key): ?string
    {
        assert($carrier instanceof Request);

        return $carrier->headers->get($key);
    }
}
