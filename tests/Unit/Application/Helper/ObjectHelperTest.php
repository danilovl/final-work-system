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

namespace App\Tests\Unit\Application\Helper;

use App\Application\Helper\ObjectHelper;
use App\Application\Traits\Entity\{
    ActiveAbleTrait,
    CreateUpdateAbleTrait,
    IdTrait,
    SimpleInformationTrait
};
use App\Domain\Event\Entity\Event;
use App\Domain\Media\Entity\Media;
use App\Domain\User\Traits\Entity\IsOwnerTrait;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ObjectHelperTest extends TestCase
{
    #[DataProvider('classProvider')]
    public function testClassUsesDeep(string|object $class, array $expectedTraits): void
    {
        $result = ObjectHelper::classUsesDeep($class);

        $this->assertEquals($expectedTraits, $result);
    }

    public static function classProvider(): Generator
    {
        yield [
            new class ( ) {}, []
        ];

        yield [
            Event::class, [
                IdTrait::class,
                CreateUpdateAbleTrait::class,
                IsOwnerTrait::class
            ]
        ];

        yield [
            Media::class, [
                IdTrait::class,
                SimpleInformationTrait::class,
                ActiveAbleTrait::class,
                CreateUpdateAbleTrait::class,
                IsOwnerTrait::class
            ]
        ];
    }
}
