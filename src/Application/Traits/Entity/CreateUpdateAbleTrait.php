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

namespace App\Application\Traits\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
trait CreateUpdateAbleTrait
{
    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $updatedAt = null;

    #[ORM\PrePersist]
    public function createUpdateAblePrePersist(): void
    {
        $this->createdAt = new DateTimeImmutable;
    }

    #[ORM\PreUpdate]
    public function createUpdateAblePreUpdate(): void
    {
        $this->updatedAt = new DateTime;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
