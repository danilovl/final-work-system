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

namespace App\Model\Event\EventDispatcher\GenericEvent;

use App\Model\Comment\Entity\Comment;
use App\Model\Event\Entity\Event;

class EventGenericEvent
{
    public Comment $comment;
    public Event $event;
}