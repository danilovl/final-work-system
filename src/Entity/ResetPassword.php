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

namespace App\Entity;

use App\Entity\Traits\{
    IdTrait,
    TimestampAbleTrait
};
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="reset_password")
 * @ORM\Entity(repositoryClass="App\Repository\ResetPasswordRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ResetPassword
{
    use IdTrait;
    use TimestampAbleTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=false, referencedColumnName="id", onDelete="CASCADE")
     */
    private ?User $user = null;

    /**
     * @ORM\Column(name="hashed_token", type="string", length=100, nullable=false, unique=true)
     */
    private ?string $hashedToken = null;

    /**
     * @ORM\Column(name="expires_at", type="datetime", nullable=false)
     */
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
