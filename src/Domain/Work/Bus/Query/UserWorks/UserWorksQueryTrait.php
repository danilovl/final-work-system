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

namespace App\Domain\Work\Bus\Query\UserWorks;

use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\Entity\WorkType;
use Doctrine\Common\Collections\ArrayCollection;

trait UserWorksQueryTrait
{
    /**
     * @return ArrayCollection<int, Work>
     */
    private function getUserWorks(
        User $user,
        string $userType,
        ?WorkType $type = null,
        ?WorkStatus $status = null
    ): ArrayCollection {
        $getUserWorksQuery = GetUserWorksQuery::create(
            user: $user,
            userType: $userType,
            type: $type,
            status: $status
        );

        /** @var GetUserWorksQueryResult $getUserWorksQueryResult */
        $getUserWorksQueryResult = $this->queryBus->handle($getUserWorksQuery);

        return $getUserWorksQueryResult->works;
    }
}
