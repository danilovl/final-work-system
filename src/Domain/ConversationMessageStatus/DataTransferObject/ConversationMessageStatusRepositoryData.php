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

namespace App\Domain\ConversationMessageStatus\DataTransferObject;

use App\Application\DataTransferObject\BaseDataTransferObject;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;

class ConversationMessageStatusRepositoryData extends BaseDataTransferObject
{
    public ?User $user = null;
    public ?Conversation $conversation = null;
    public ?ConversationMessageStatusType $type = null;
}
