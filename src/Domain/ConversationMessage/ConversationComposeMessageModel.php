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

namespace App\Domain\ConversationMessage;

use App\Application\Traits\Model\ContentAwareTrait;
use Traversable;

class ConversationComposeMessageModel
{
    use ContentAwareTrait;

    public ?string $name = null;
    public ?Traversable $conversation = null;
}
