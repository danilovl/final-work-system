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

namespace App\Application\Form\Transformer;

use App\Application\Exception\InvalidArgumentException;
use Symfony\Component\Form\DataTransformerInterface;

class TrimTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('The value must be a string.');
        }

        return trim($value);
    }

    public function reverseTransform(mixed $value): mixed
    {
        return $value;
    }
}
