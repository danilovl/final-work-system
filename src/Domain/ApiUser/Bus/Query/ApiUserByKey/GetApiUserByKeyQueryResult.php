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

namespace App\Domain\ApiUser\Bus\Query\ApiUserByKey;

use App\Domain\ApiUser\Entity\ApiUser;

readonly class GetApiUserByKeyQueryResult
{
    public function __construct(
        public ?ApiUser $apiUser
    ) {}
}
