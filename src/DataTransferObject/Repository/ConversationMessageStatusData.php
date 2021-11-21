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

namespace App\DataTransferObject\Repository;

use App\DataTransferObject\BaseDataTransferObject;
use App\Model\Conversation\Entity\Conversation;
use App\Model\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Model\User\Entity\User;

class ConversationMessageStatusData extends BaseDataTransferObject
{
    public ?User $user = null;
    public ?Conversation $conversation = null;
    public ?ConversationMessageStatusType $type = null;
}
