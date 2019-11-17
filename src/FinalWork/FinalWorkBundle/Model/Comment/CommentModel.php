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

namespace FinalWork\FinalWorkBundle\Model\Comment;

use FinalWork\FinalWorkBundle\Entity\Comment;
use FinalWork\FinalWorkBundle\Model\Traits\ContentAwareTrait;
use Symfony\Component\Validator\Constraints as Assert;

class CommentModel
{
    use ContentAwareTrait;

    /**
     * @Assert\NotBlank()
     */
    public $owner;

    /**
     * @Assert\NotBlank()
     */
    public $event;

    /**
     * @param Comment $comment
     * @return CommentModel
     */
    public static function fromComment(Comment $comment): self
    {
        $model = new self();
        $model->content = $comment->getContent();
        $model->owner = $comment->getOwner();
        $model->event = $comment->getEvent();

        return $model;
    }
}
