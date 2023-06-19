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

namespace App\Domain\Event\EventDispatcher\GenericEvent;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Event\Entity\Event;

readonly class EventCommentGenericEvent
{
    public function __construct(public Comment $comment) {}
}
