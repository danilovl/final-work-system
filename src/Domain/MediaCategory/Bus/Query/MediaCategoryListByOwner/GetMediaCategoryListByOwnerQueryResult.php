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

namespace App\Domain\MediaCategory\Bus\Query\MediaCategoryListByOwner;

use App\Domain\MediaCategory\Entity\MediaCategory;

readonly class GetMediaCategoryListByOwnerQueryResult
{
    /**
     * @param MediaCategory[] $mediaCategories
     */
    public function __construct(
        public array $mediaCategories
    ) {}
}
