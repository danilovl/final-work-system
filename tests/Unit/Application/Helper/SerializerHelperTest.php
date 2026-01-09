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

use App\Application\Helper\SerializerHelper;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Serializer\Serializer;

class SerializerHelperTest extends TestCase
{
    public function testGetBaseSerializer(): void
    {
        $this->assertInstanceOf(Serializer::class, SerializerHelper::getBaseSerializer());
    }

    public function testConvertToObject(): void
    {
        $object = new stdClass;
        $object->id = 1;
        $object->username = 'username';
        $object->password = 'password';

        $result = SerializerHelper::convertToObject($object, User::class);

        $expected = new User;
        $expected->setId(1);
        $expected->setUsername('username');
        $expected->setPassword('password');

        $this->assertEquals($expected, $result);
    }
}
