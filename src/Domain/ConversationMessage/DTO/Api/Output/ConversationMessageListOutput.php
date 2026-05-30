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

namespace App\Domain\ConversationMessage\DTO\Api\Output;

use App\Application\DTO\Api\Output\BaseListOutput;
use App\Domain\ConversationMessage\DTO\Api\ConversationMessageDetailDTO;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups(['output'])]
readonly class ConversationMessageListOutput extends BaseListOutput
{
    /**
     * @return ConversationMessageDetailDTO[]
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
