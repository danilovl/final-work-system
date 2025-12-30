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

namespace App\Domain\Event\DTO\Api\Input;

use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

readonly class EventCreateInput
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $name,

        #[Assert\NotBlank]
        #[Assert\Type(DateTime::class)]
        public DateTime $start,

        #[Assert\NotBlank]
        #[Assert\Type(DateTime::class)]
        public DateTime $end,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $typeId,

        public ?int $addressId = null
    ) {}
}
