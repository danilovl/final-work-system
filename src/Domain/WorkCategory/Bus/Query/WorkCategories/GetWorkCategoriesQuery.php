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

namespace App\Domain\WorkCategory\Bus\Query\WorkCategories;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetWorkCategoriesQuery implements QueryInterface
{
    /**
     * @param int[] $ids
     */
    private function __construct(
        public array $ids
    ) {}

    /**
     * @param int[] $ids
     */
    public static function create(array $ids): self
    {
        return new self($ids);
    }
}
