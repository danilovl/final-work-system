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

namespace App\Tests;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;
use SplFileInfo;

class ReplacerFinal
{
    private const array ALLOW_METHODS = ['replace', 'replaceBack'];
    private const string FILE_NAME = 'replaceClasses.txt';

    private array $classes = [];

    public function __construct(?array $argv)
    {
        require 'bootstrap.php';

        $method = $argv[1] ?? null;
        if (in_array($method, self::ALLOW_METHODS, true)) {
            $this->{$method}();
        }
    }

    private function replace(): void
    {
        if (file_exists(self::FILE_NAME)) {
            $this->replaceBack();
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../tests'));
        /** @var SplFileInfo[] $phpFiles */
        $phpFiles = new RegexIterator($files, '~\.php$~');

        foreach ($phpFiles as $file) {
            $filePath = $file->getPathname();
            /** @var string $contents */
            $contents = file_get_contents($filePath);

            $matches = [];
            preg_match_all('/^use\s+([^;{]+)(?:{([^}]+)})?;/m', $contents, $matches, PREG_SET_ORDER);

            $imports = [];
            foreach ($matches as $match) {
                $namespace = mb_trim($match[1]);

                if (!empty($match[2])) {
                    $classes = array_map('trim', explode(',', $match[2]));
                    foreach ($classes as $class) {
                        $imports[] = $namespace . $class;
                    }
                } else {
                    $imports[] = $namespace;
                }
            }

            foreach ($imports as $class) {
                if (!class_exists($class)) {
                    continue;
                }

                $reflectionClass = new ReflectionClass($class);

                if ($reflectionClass->isFinal() || $reflectionClass->isReadOnly()) {
                    $filename = $reflectionClass->getFileName();
                    if ($filename) {
                        $this->removeClassAccess($filename);
                    }
                }
            }

            $serialized = serialize($this->classes);
            file_put_contents(self::FILE_NAME, $serialized);
        }
    }

    private function replaceBack(): void
    {
        if (!file_exists(self::FILE_NAME)) {
            return;
        }

        /** @var string $serialized */
        $serialized = file_get_contents(self::FILE_NAME);
        /** @var array $classes */
        $classes = unserialize($serialized);
        $this->classes = $classes;

        foreach ($this->classes as $fileName => $content) {
            file_put_contents($fileName, $content);
        }

        unlink(self::FILE_NAME);
    }

    private function removeClassAccess(string $fileName): void
    {
        if (str_contains($fileName, 'vendor')) {
            return;
        }

        /** @var string $classContent */
        $classContent = file_get_contents($fileName);
        if (!isset($this->classes[$fileName])) {
            $this->classes[$fileName] = $classContent;
        }

        $classContent = str_replace(['readonly class', 'readonly final class'], 'class', $classContent);

        file_put_contents($fileName, $classContent);
    }
}

/** @var array|null $arg */
$arg = $_SERVER['argv'];
(new ReplacerFinal($arg));
