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

namespace App\Tests\Integration\Infrastructure\DependencyInjection;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionProperty;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ServicesResetter;
use Symfony\Contracts\Service\ResetInterface;

class ResettableServicesTest extends KernelTestCase
{
    private const string SOURCE_DIR = __DIR__ . '/../../../../src';

    public function testEveryResettableServiceIsResetBetweenRequests(): void
    {
        $servicesResetter = static::getContainer()->get('services_resetter');
        $this->assertInstanceOf(ServicesResetter::class, $servicesResetter);

        $resetMethodsProperty = new ReflectionProperty(ServicesResetter::class, 'resetMethods');
        /** @var array<string, string[]> $resetMethods */
        $resetMethods = $resetMethodsProperty->getValue($servicesResetter);
        $resetServiceIds = array_keys($resetMethods);

        $resettableClasses = $this->findResettableClasses();
        $this->assertNotEmpty($resettableClasses);

        $notResetClasses = array_values(array_diff($resettableClasses, $resetServiceIds));

        $this->assertSame([], $notResetClasses);
    }

    /**
     * @return class-string[]
     */
    private function findResettableClasses(): array
    {
        $classes = [];
        $directoryIterator = new RecursiveDirectoryIterator(self::SOURCE_DIR);
        $files = new RecursiveIteratorIterator($directoryIterator);
        $sourceDir = realpath(self::SOURCE_DIR);

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = mb_substr($file->getRealPath(), mb_strlen((string) $sourceDir) + 1, -4);
            $className = 'App\\' . str_replace('/', '\\', $relativePath);

            if (!class_exists($className)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($className);
            $isResettable = $reflectionClass->implementsInterface(ResetInterface::class);

            if (!$isResettable || $reflectionClass->isAbstract()) {
                continue;
            }

            $classes[] = $className;
        }

        sort($classes);

        return $classes;
    }
}
