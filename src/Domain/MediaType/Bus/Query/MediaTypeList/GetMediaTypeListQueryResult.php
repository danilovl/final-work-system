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

namespace App\Domain\MediaType\Bus\Query\MediaTypeList;

use App\Domain\MediaType\Entity\MediaType;

readonly class GetMediaTypeListQueryResult
{
    /**
     * @param MediaType[] $mediaTypes
     */
    public function __construct(
        public array $mediaTypes
    ) {}
}
