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

namespace App\Tests\Unit\Application\DataTransferObject;

use App\Application\DataTransferObject\BaseDataTransferObject;
use App\Application\Exception\PropertyNotExistException;
use PHPUnit\Framework\TestCase;

class BaseDataTransferObjectTest extends TestCase
{
    private BaseDataTransferObject $dataTransferObject;

    protected function setUp(): void
    {
        $dataTransferObject = new class() extends BaseDataTransferObject {
            public ?string $user;

            public ?string $conversation;

            public ?string $type;
        };

        $this->dataTransferObject = $dataTransferObject::createFromArray($this->getData());
    }

    public function testCreateFromArray(): void
    {
        $dataTransferObject = $this->dataTransferObject::createFromArray($this->getData());

        $this->assertEquals(
            $this->getData(),
            $dataTransferObject->toArray()
        );
    }

    public function testCreateFromArrayPropertyNotExist(): void
    {
        $this->expectException(PropertyNotExistException::class);

        $this->dataTransferObject::createFromArray([
            'data' => null
        ]);
    }

    public function testToJson(): void
    {
        $this->assertEquals(
            json_encode($this->getData()),
            $this->dataTransferObject->toJson()
        );
    }

    public function testToArray(): void
    {
        $this->assertEquals(
            $this->getData(),
            $this->dataTransferObject->toArray()
        );
    }

    public function testCreateFromJson(): void
    {
        /** @var string $json */
        $json = json_encode($this->getData());
        $dataTransferObject = $this->dataTransferObject::createFromJson($json);

        $this->assertEquals(
            $this->getData(),
            $dataTransferObject->toArray()
        );
    }

    public function testCreateFromArrayRequired(): void
    {
        $dataTransferObject = $this->dataTransferObject::createFromArray($this->getData(), false);

        $this->assertEquals(
            $this->getData(),
            $dataTransferObject->toArray()
        );
    }

    private function getData(): array
    {
        return [
            'user' => null,
            'conversation' => null,
            'type' => null
        ];
    }
}
