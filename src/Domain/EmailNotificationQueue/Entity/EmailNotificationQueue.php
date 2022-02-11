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

namespace App\Domain\EmailNotificationQueue\Entity;

use App\Application\Constant\TranslationConstant;
use App\Application\Traits\Entity\{
    IdTrait,
    CreateAbleTrait
};
use App\Domain\EmailNotificationQueue\Repository\EmailNotificationQueueRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'email_notification')]
#[ORM\Entity(repositoryClass: EmailNotificationQueueRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EmailNotificationQueue
{
    use IdTrait;
    use CreateAbleTrait;

    #[ORM\Column(name: '`subject`', type: Types::STRING, nullable: false)]
    private ?string $subject = null;

    #[ORM\Column(name: '`to`', type: Types::STRING, nullable: false)]
    private ?string $to = null;

    #[ORM\Column(name: '`from`', type: Types::STRING, nullable: false)]
    private ?string $from = null;

    #[ORM\Column(name: 'body', type: Types::TEXT, nullable: false)]
    private ?string $body = null;

    #[ORM\Column(name: 'success', type: Types::BOOLEAN, options: ['default' => '0'])]
    private bool $success = false;

    #[ORM\Column(name: 'sended_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $sendedAt = null;

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function setTo(string $to): void
    {
        $this->to = $to;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    public function getSendedAt(): ?DateTime
    {
        return $this->sendedAt;
    }

    public function setSendedAt(?DateTime $sendedAt): void
    {
        $this->sendedAt = $sendedAt;
    }

    public function __toString(): string
    {
        return $this->subject ?: TranslationConstant::EMPTY;
    }
}
