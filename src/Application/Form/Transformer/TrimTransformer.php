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

use Override;
use Symfony\Component\Form\DataTransformerInterface;

class TrimTransformer implements DataTransformerInterface
{
    #[Override]
    public function transform(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return trim($value);
    }

    #[Override]
    public function reverseTransform(mixed $value): mixed
    {
        return $value;
    }
}
