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

use App\Application\Exception\PropertyNotExistException;
use App\Tests\Mock\Unit\Application\DataTransferObject\DataTransferObjectWithConstructor;
use App\Tests\Mock\Unit\Application\DataTransferObject\DataTransferObjectWithoutConstructor;
use PHPUnit\Framework\TestCase;

class BaseDataTransferObjectTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $dataTransferObject = DataTransferObjectWithoutConstructor::createFromArray($this->getData());

        $this->assertEquals(
            $this->getData(),
            $dataTransferObject->toArray()
        );
    }

    public function testCreateFromArrayPropertyNotExist(): void
    {
        $this->expectException(PropertyNotExistException::class);

        DataTransferObjectWithoutConstructor::createFromArray([
            'data' => null
        ]);
    }

    public function testToJson(): void
    {
        $dataTransferObject = DataTransferObjectWithoutConstructor::createFromArray($this->getData());

        $this->assertEquals(
            json_encode($this->getData()),
            $dataTransferObject->toJson()
        );
    }

    public function testToArray(): void
    {
        $dataTransferObject = DataTransferObjectWithoutConstructor::createFromArray($this->getData());

        $this->assertEquals(
            $this->getData(),
            $dataTransferObject->toArray()
        );
    }

    public function testCreateFromJson(): void
    {
        /** @var string $json */
        $json = json_encode($this->getData());
        $dataTransferObject = DataTransferObjectWithoutConstructor::createFromJson($json);

        $this->assertEquals(
            $this->getData(),
            $dataTransferObject->toArray()
        );
    }

    public function testCreateFromArrayRequired(): void
    {
        $dataTransferObject = DataTransferObjectWithoutConstructor::createFromArray($this->getData(), false);

        $this->assertEquals(
            $this->getData(),
            $dataTransferObject->toArray()
        );
    }

    public function testCreateFromArrayWithConstructor(): void
    {
        $dataTransferObject = DataTransferObjectWithConstructor::createFromArray($this->getData());

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
