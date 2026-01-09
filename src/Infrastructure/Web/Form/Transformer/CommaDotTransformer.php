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

namespace App\Infrastructure\Web\Form\Transformer;

use App\Application\Exception\RuntimeException;
use Override;
use Symfony\Component\Form\DataTransformerInterface;

class CommaDotTransformer implements DataTransformerInterface
{
    #[Override]
    public function transform(mixed $value): string
    {
        return $this->replace($value);
    }

    #[Override]
    public function reverseTransform(mixed $value): string
    {
        return $this->replace($value);
    }

    private function replace(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (!is_string($value) && !is_numeric($value)) {
            throw new RuntimeException(sprintf('"%s" is not a valid type.', gettype($value)));
        }

        return str_replace(',', '.', (string) $value);
    }
}
