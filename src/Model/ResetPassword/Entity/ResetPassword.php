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

namespace App\Model\ResetPassword\Entity;

use App\Model\ResetPassword\Repository\ResetPasswordRepository;
use App\Model\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\{
    IdTrait,
    TimestampAbleTrait
};
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'reset_password')]
#[ORM\Entity(repositoryClass: ResetPasswordRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ResetPassword
{
    use IdTrait;
    use TimestampAbleTrait;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(name: 'hashed_token', type: Types::STRING, length: 100, unique: true, nullable: false)]
    private ?string $hashedToken = null;

    #[ORM\Column(name: 'expires_at', type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?DateTime $expiresAt = null;

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string|null
     */
    public function getHashedToken(): ?string
    {
        return $this->hashedToken;
    }

    /**
     * @param string|null $hashedToken
     */
    public function setHashedToken(string $hashedToken): void
    {
        $this->hashedToken = $hashedToken;
    }

    /**
     * @return DateTime|null
     */
    public function getExpiresAt(): ?DateTime
    {
        return $this->expiresAt;
    }

    /**
     * @param DateTime|null $expiresAt
     */
    public function setExpiresAt(DateTime $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt->getTimestamp() <= time();
    }
}
