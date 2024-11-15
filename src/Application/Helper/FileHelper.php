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

namespace App\Application\Helper;

class FileHelper
{
    public static function createTmpFile(
        string $extension,
        string $data,
        string $prefix = 'temp-file'
    ): string {
        /** @var string $file */
        $file = tempnam(sys_get_temp_dir(), $prefix);
        $newName = str_replace(pathinfo($file, PATHINFO_EXTENSION), $extension, $file);
        rename($file, $newName);

        file_put_contents($newName, $data);

        return $newName;
    }
}

declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\Helper;

class FileHelper
{
    public static function createTmpFile(
        string $extension,
        string $data,
        string $prefix = 'temp-file'
    ): string {
        /** @var string $file */
        $file = tempnam(sys_get_temp_dir(), $prefix);
        $newName = str_replace(pathinfo($file, PATHINFO_EXTENSION), $extension, $file);
        rename($file, $newName);

        file_put_contents($newName, $data);

        return $newName;
    }

    public static function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) {
            return false;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        /** @var array $items */
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            $itemPath = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($itemPath)) {
                self::deleteDirectory($itemPath);
            } else {
                unlink($itemPath);
            }
        }

        return rmdir($dir);
    }
}
