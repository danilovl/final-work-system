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

namespace App\EventDispatcher\GenericEvent;

use App\Entity\User;

class UserGenericEvent
{
    public User $user;
    public User $owner;

    public function __construct(User $user, User $owner)
    {
        $this->user = $user;
        $this->owner = $owner;
    }
}