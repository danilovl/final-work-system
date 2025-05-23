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

namespace App\Domain\User\EventDispatcher\GenericEvent;

use App\Application\Exception\PropertyValueIsNullException;
use App\Domain\User\Entity\User;

readonly class UserGenericEvent
{
    public function __construct(public User $user, public ?User $owner = null) {}

    public function getOwnerMust(): User
    {
        if (!$this->owner) {
            throw new PropertyValueIsNullException('Owner is null.');
        }

        return $this->owner;
    }
}
