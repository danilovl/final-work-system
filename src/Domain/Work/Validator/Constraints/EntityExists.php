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

namespace App\Domain\Work\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class EntityExists extends Constraint
{
    public string $message = 'app.validator.entity_exists';

    /**
     * @param class-string<object> $class
     * @param string[]|null $groups
     */
    public function __construct(
        /** @var class-string<object> */
        public string $class,
        public string $property = 'id',
        ?array $groups = null,
        mixed $payload = null,
        ?string $message = null
    ) {
        parent::__construct([], $groups, $payload);

        $this->message = $message ?? $this->message;
    }
}
