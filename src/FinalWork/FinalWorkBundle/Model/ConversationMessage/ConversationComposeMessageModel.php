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

namespace FinalWork\FinalWorkBundle\Model\ConversationMessage;

use FinalWork\FinalWorkBundle\Model\Traits\ContentAwareTrait;
use Symfony\Component\Validator\Constraints as Assert;

class ConversationComposeMessageModel
{
    use ContentAwareTrait;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @Assert\NotBlank()
     */
    public $conversation;
}
