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

readonly class MockSourceEntity
{
    public function __construct(
        private int $id,
        private ?MockTargetEntity $targetEntity = null,
        private ?MockTargetEmptyEntity $targetEmptyEntity = null
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getTargetEntity(): ?MockTargetEntity
    {
        return $this->targetEntity;
    }

    public function getTargetEmptyEntity(): ?MockTargetEmptyEntity
    {
        return $this->targetEmptyEntity;
    }
}
