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

namespace App\Tests\Mock\Unit\Infrastructure\Event\EventListener;

use Doctrine\Common\Collections\Collection;

class MockMultiRelationEntity
{
    public MockTargetEntity $uninitialized;

    /**
     * @param Collection<int, MockTargetEntity>|null $targets
     */
    public function __construct(
        public ?MockTargetEntity $first = null,
        public ?MockTargetEntity $second = null,
        public ?Collection $targets = null
    ) {}
}
