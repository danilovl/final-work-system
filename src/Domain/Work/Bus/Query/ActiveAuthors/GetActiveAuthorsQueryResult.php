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

namespace App\Domain\Work\Bus\Query\ActiveAuthors;

use App\Domain\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

readonly class GetActiveAuthorsQueryResult
{
    /**
     * @param ArrayCollection<int, User> $authors
     */
    public function __construct(
        public ArrayCollection $authors
    ) {}
}
