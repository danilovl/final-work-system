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

namespace App\Infrastructure\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class EntityRelationValidatorAttribute
{
    /**
     * @param string|null $property
     */
    public function __construct(
        public string $sourceEntity,
        public string $targetEntity,
        public ?string $property = null
    ) {}
}
