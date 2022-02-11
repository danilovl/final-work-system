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

namespace App\Application\Traits\Entity;

trait IsReadTrait
{
    public bool $read = false;

    public function setRead(bool $read): void
    {
        $this->read = $read;
    }

    public function isRead(): bool
    {
        return $this->read;
    }
}
