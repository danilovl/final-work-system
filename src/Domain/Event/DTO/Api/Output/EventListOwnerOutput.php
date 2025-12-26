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

use App\Domain\Event\DTO\Api\EventDTO;

readonly class EventListOwnerOutput extends BaseListOutput
{
    /**
     * @return EventDTO[]
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
