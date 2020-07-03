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

namespace App\GraphQL\Resolver;

use Overblog\GraphQLBundle\Resolver\ResolverMap;

class NotDoctrineResolverMap extends ResolverMap
{
    protected function map()
    {
        return [
            'not_doctrine' => [
                'getRandomValue' => static function (): int {
                    return rand(0, 100);
                },
            ]
        ];
    }
}