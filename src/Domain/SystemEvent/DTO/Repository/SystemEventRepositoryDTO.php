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

namespace App\Domain\SystemEvent\DTO\Repository;

use App\Application\Exception\InvalidArgumentException;
use App\Domain\User\Entity\User;

class SystemEventRepositoryDTO
{
    public function __construct(
        public ?User $recipient = null,
        public ?bool $viewed = null,
        public ?int $limit = null,
        public ?int $offset = null
    ) {}

     public function getRecipientNotNull(): User
     {
         if ($this->recipient === null) {
             throw new InvalidArgumentException('Recipient is null');
         }

         return $this->recipient;
     }
}
