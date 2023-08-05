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

namespace App\Tests;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;

class ReplacerFinal
{
    private const ALLOW_METHODS = ['replace', 'replaceBack'];
    private const FILE_NAME = 'replaceClasses.txt';

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
        $phpFiles = new RegexIterator($files, '~\.php$~');

        foreach ($phpFiles as $file) {
            $filePath = $file->getPathname();
            $contents = file_get_contents($filePath);

            $matches = [];
            preg_match_all('~use\s+(\S+);~', $contents, $matches);

            foreach ($matches[1] as $class) {
                if (!class_exists($class)) {
                    continue;
                }

                $reflectionClass = new ReflectionClass($class);

                if ($reflectionClass->isFinal() || $reflectionClass->isReadOnly()) {
                    $filename = $reflectionClass->getFileName();
                    if ($filename) {
                        $this->removeClassAccess($reflectionClass->getFileName());
                    }
                }
            }

            $serialized = serialize($this->classes);
            file_put_contents(self::FILE_NAME, $serialized);
        }
    }

    private function replaceBack(): void
    {
        $serialized = file_get_contents(self::FILE_NAME);
        $this->classes = unserialize($serialized);

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

        $classContent = file_get_contents($fileName);
        if (!isset($this->classes[$fileName])) {
            $this->classes[$fileName] = $classContent;
        }

        $classContent = str_replace(['readonly class', 'readonly final class'], 'class', $classContent);

        file_put_contents($fileName, $classContent);
    }
}

(new ReplacerFinal($_SERVER['argv']));
