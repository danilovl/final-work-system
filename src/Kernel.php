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

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Override;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);
    }

    #[Override]
    public function getCacheDir(): string
    {
        if (($_SERVER['APP_E2E_TEST'] ?? null) === '1') {
            return $this->getProjectDir() . '/var/cache/e2e_' . $this->environment;
        }

        return parent::getCacheDir();
    }
}
