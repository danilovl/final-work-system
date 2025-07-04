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

namespace App;

use App\Infrastructure\OpenTelemetry\Kernel\KernelRegistration;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct(string $environment, bool $debug, bool $isEnableOpenTelemetry = true)
    {
        parent::__construct($environment, $debug);

        if ($isEnableOpenTelemetry && extension_loaded('opentelemetry')) {
            KernelRegistration::registration();
        }
    }
}
