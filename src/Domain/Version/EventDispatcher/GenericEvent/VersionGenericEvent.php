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

namespace App\Domain\Version\EventDispatcher\GenericEvent;

use App\Domain\Media\Entity\Media;

class VersionGenericEvent
{
    public function __construct(public Media $media) {}
}
