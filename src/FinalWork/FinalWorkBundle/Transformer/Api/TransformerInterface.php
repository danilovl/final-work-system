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

namespace FinalWork\FinalWorkBundle\Transformer\Api;

interface TransformerInterface
{
    /**
     * @param string $domain
     * @param array $fields
     * @param $object
     * @return array
     */
    public function transform(string $domain, array $fields, $object): array;
}