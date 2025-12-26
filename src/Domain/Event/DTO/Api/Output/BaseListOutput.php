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

namespace App\Domain\Event\DTO\Api\Output;

use Symfony\Component\Serializer\Attribute\Groups;

#[Groups(['output'])]
readonly class BaseListOutput
{
    public function __construct(
        public int $numItemsPerPage,
        public int $totalCount,
        public int $currentItemCount,
        public array $result
    ) {}
}
