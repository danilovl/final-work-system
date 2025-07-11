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

namespace App\Tests\Mock\Application\Mapper;

readonly class MockEntity
{
    public function __construct(
        public string $name,
        public int $age,
        public ?string $email = null
    ) {}
}
