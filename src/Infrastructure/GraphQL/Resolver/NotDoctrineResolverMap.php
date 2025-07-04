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

namespace App\Infrastructure\GraphQL\Resolver;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;

class NotDoctrineResolverMap extends ResolverMap
{
    #[Override]
    protected function map(): array
    {
        return [
            'not_doctrine' => [
                'getRandomValue' => static fn (): int => rand(0, 100),
            ]
        ];
    }
}
