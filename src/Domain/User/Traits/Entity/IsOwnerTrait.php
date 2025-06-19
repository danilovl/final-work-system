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

namespace App\Domain\User\Traits\Entity;

use App\Domain\User\Entity\User;

trait IsOwnerTrait
{
    public function isOwner(User $user): bool
    {
        return $this->getOwner()->getId() === $user->getId();
    }
}
