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

namespace App\Tests\Application\DataTransferObject;

use App\Application\DataTransferObject\{
    App\Application\Interfaces\DataTransferObject\DataTransferObjectInterface,
    BaseDataTransferObject};
use PHPUnit\Framework\TestCase;

class BaseDataTransferObjectTest extends TestCase
{
    private \App\Application\Interfaces\DataTransferObject\DataTransferObjectInterface $dataTransferObject;

    protected function setUp(): void
    {
        $dataTransferObject = new class extends BaseDataTransferObject {
            public ?string $user;
            public ?string $conversation;
            public ?string $type;
        };

        $this->dataTransferObject = $dataTransferObject::createFromArray($this->getData());
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

    private function getData(): array
    {
        return [
            'user' => null,
            'conversation' => null,
            'type' => null
        ];
    }
}
