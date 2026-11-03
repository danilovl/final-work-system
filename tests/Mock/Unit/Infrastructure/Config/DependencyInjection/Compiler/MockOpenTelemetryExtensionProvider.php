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

namespace App\Tests\Mock\Unit\Infrastructure\Config\DependencyInjection\Compiler;

use App\Infrastructure\Provider\OpenTelemetryExtensionProvider;

class MockOpenTelemetryExtensionProvider extends OpenTelemetryExtensionProvider
{
    public function __construct(private bool $loaded) {}

    public function setLoaded(bool $loaded): void
    {
        $this->loaded = $loaded;
    }

    public function isExtensionLoaded(): bool
    {
        return $this->loaded;
    }
}
