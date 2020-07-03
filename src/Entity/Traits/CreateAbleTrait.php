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

namespace App\Entity\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifecycleCallbacks()
 */
trait CreateAbleTrait
{
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private ?DateTime $createdAt = null;

    /**
     * @ORM\PrePersist()
     */
    public function createAblePrePersist(): void
    {
        $this->createdAt = new DateTime;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
