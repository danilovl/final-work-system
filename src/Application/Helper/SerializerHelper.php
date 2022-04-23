<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\Helper;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\{
    ObjectNormalizer,
    DateTimeNormalizer
};

class SerializerHelper
{
    public static function getBaseSerializer(): Serializer
    {
        return new Serializer([new DateTimeNormalizer, new ObjectNormalizer], [new JsonEncoder]);
    }
}
