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

use App\Application\Traits\Model\ContentAwareTrait;

class ConversationComposeMessageModel
{
    use ContentAwareTrait;

    public ?string $name = null;
    private iterable $conversation = [];

    public function getConversation(): iterable
    {
        return $this->conversation;
    }

    public function setConversation(mixed $conversation): void
    {
        if (!is_iterable($conversation)) {
            $conversation = [$conversation];
        }

        $this->conversation = $conversation;
    }
}
