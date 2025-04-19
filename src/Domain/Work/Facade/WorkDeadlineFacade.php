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

namespace App\Domain\Work\Facade;

use App\Domain\Work\Entity\Work;
use App\Domain\Work\Repository\WorkRepository;
use Webmozart\Assert\Assert;

readonly class WorkDeadlineFacade
{
    public function __construct(private WorkRepository $workRepository) {}

    /**
     * @return Work[]
     */
    public function getWorksAfterDeadline(int $offset, int $limit): array
    {
        /** @var array $result */
        $result = $this->workRepository
            ->getWorksAfterDeadline()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        Assert::allIsInstanceOf($result, Work::class);

        return $result;
    }
}
