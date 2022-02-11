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

namespace App\Application\Constant;

enum TranslationConstant
{
    public const EMPTY = 'empty';
    public const DEFAULT_START_KEY = 'app';
    public const FLASH_START_KEY = 'flash';
    public const FLASH_DOMAIN = 'flashes';
}
