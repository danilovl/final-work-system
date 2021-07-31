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

namespace App\Helper;

class FileHelper
{
    public static function createTmpFile(
        string $extension,
        string $data,
        string $prefix = 'temp-file'
    ): string|false {
        $file = tempnam(sys_get_temp_dir(), $prefix);
        $newName = str_replace(pathinfo($file, PATHINFO_EXTENSION), $extension, $file);
        rename($file, $newName);

        file_put_contents($newName, $data);

        return $newName;
    }
}
