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

namespace App\Domain\Media\Constant;

enum MediaConstant: string
{
    case WEB_PATH_TO_UPLOAD_FOLDER = 'upload/';
    case SERVER_PATH_TO_PUBLIC_FOLDER = __DIR__ . '/../../../public';
}
