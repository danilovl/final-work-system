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

namespace App\Domain\User\DTO\Api\Output;

use App\Application\DTO\Api\Output\BaseListOutput;

readonly class UserListOutput extends BaseListOutput
{
    /**
     * @param int $numItemsPerPage
     * @param int $totalCount
     * @param int $currentItemCount
     * @param UserListItemDTO[] $result
     */
    public function __construct(
        int $numItemsPerPage,
        int $totalCount,
        int $currentItemCount,
        array $result
    ) {
        parent::__construct($numItemsPerPage, $totalCount, $currentItemCount, $result);
    }

    /**
     * @return UserListItemDTO[]
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
