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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\HasLifecycleCallbacks()
 */
trait TimestampAbleTrait
{
    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private ?DateTime $createdAt = null;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    private ?DateTime $updatedAt = null;

    /**
     * @ORM\PrePersist()
     */
    public function timestampAblePrePersist(): void
    {
        $this->createdAt = new DateTime;
        $this->updatedAt = new DateTime;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function timestampAblePreUpdate(): void
    {
        $this->updatedAt = new DateTime;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
