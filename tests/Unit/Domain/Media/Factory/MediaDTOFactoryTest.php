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

namespace App\Tests\Unit\Domain\Media\Factory;

use App\Domain\Media\Entity\Media;
use App\Domain\Media\Factory\MediaDTOFactory;
use App\Domain\MediaType\Entity\MediaType;
use App\Tests\Mock\Domain\Work\WorkApiDTOTraitMock;
use PHPUnit\Framework\TestCase;

class MediaDTOFactoryTest extends TestCase
{
    use WorkApiDTOTraitMock;

    public function testCreate(): void
    {
        $media = $this->createMedia();
        $media->setDescription('<p>Version</p>');
        $media->setMediaSize(1_933_307);
        $media->setWork($this->createWork());

        $dto = new MediaDTOFactory($this->createWorkDetailDTOFactory())->create($media);

        $this->assertSame(
            ['id', 'type', 'name', 'description', 'mediaName', 'mediaSize', 'originalExtension', 'owner', 'work'],
            array_keys(get_object_vars($dto))
        );
        $this->assertSame(['id' => 1, 'name' => 'WORK_VERSION', 'description' => null], get_object_vars($dto->type));
        $this->assertSame(1_933_307, $dto->mediaSize);
        $this->assertSame(36, $dto->owner->id);
        $this->assertNotNull($dto->work);
        $this->assertSame(11, $dto->work->id);
        $this->assertSame('2026-06-30 14:05:09', $dto->work->deadline);
    }

    public function testMediaWithoutWork(): void
    {
        $dto = new MediaDTOFactory($this->createWorkDetailDTOFactory())->create($this->createMedia());

        $this->assertNull($dto->work);
        $this->assertNull($dto->mediaSize);
        $this->assertNull($dto->description);
    }

    private function createMedia(): Media
    {
        $type = new MediaType;
        $type->setId(1);
        $type->setName('WORK_VERSION');
        $type->setDescription(null);

        $media = new Media;
        $media->setId(86);
        $media->setType($type);
        $media->setName('1.2');
        $media->setMediaName('43bbeeac.pdf');
        $media->setOriginalExtension(null);
        $media->setOwner($this->createUser(36));

        return $media;
    }
}
