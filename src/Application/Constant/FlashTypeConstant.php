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

namespace App\Application\Constant;

enum FlashTypeConstant: string
{
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case ERROR = 'error';

    case CREATE_SUCCESS = 'create.success';
    case CREATE_WARNING = 'create.warning';
    case CREATE_ERROR = 'create.error';

    case SAVE_SUCCESS = 'save.success';
    case SAVE_WARNING = 'save.warning';
    case SAVE_ERROR = 'save.failure';

    case DELETE_SUCCESS = 'delete.success';
    case DELETE_WARNING = 'delete.warning';
    case DELETE_ERROR = 'delete.error';

    public function getMainType(): self
    {
        return match ($this) {
            self::CREATE_SUCCESS, self::SAVE_SUCCESS, self::DELETE_SUCCESS => self::SUCCESS,
            self::CREATE_ERROR, self::SAVE_ERROR, self::DELETE_ERROR => self::ERROR,
            default => $this
        };
    }
}
