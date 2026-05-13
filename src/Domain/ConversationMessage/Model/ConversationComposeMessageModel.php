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

namespace App\Domain\ConversationMessage\Model;

use App\Domain\Conversation\Entity\Conversation;

class ConversationComposeMessageModel
{
    public ?int $id = null;

    public ?string $name = null;

    public array|Conversation $conversation = [] {
        set {
            $this->conversation = is_array($value) ? $value : [$value];
        }
    }

    public string $content;

    /**
     * @return Conversation[]
     */
    public function getConversations(): array
    {
        /** @var Conversation[] $result */
        $result = $this->conversation;

        return $result;
    }
}
