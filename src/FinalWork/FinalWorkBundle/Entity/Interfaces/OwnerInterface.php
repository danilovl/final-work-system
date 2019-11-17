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

namespace FinalWork\FinalWorkBundle\Controller\Interfaces;

use FinalWork\SonataUserBundle\Entity\User;

interface OwnerInterface
{
    /**
     * @param User $user
     * @return bool
     */
    public function isOwner(User $user): bool;
}