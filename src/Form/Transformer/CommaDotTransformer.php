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

namespace App\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

class CommaDotTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): string
    {
        return str_replace(',', '.', (string) $value);
    }

    public function reverseTransform(mixed $value): mixed
    {
        return $value;
    }
}
