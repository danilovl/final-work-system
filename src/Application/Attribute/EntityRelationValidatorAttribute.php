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

namespace App\Application\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class EntityRelationValidatorAttribute
{
    public function __construct(
        public string $sourceEntity,
        public string $targetEntity
    ) {}
}
