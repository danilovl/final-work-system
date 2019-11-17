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

namespace FinalWork\FinalWorkBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

class CommaDotTransformer implements DataTransformerInterface
{
    /**
     * @param mixed $number
     * @return mixed
     */
    public function transform($number)
    {
        return str_replace(',', '.', $number);
    }

    /**
     * @param mixed $number
     * @return mixed
     */
    public function reverseTransform($number)
    {
        return $number;
    }
}
