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

namespace App\Domain\SystemEvent\Constant;

enum SystemEventStatusConstant
{
    final public const string ALL = 'all';

    final public const string READ = 'read';

    final public const string UNREAD = 'unread';
}
