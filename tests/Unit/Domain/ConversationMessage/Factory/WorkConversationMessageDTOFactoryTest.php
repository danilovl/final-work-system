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

namespace App\Tests\Unit\Domain\ConversationMessage\Factory;

use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessage\Factory\WorkConversationMessageDTOFactory;
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Tests\Mock\Domain\Work\WorkApiDTOTraitMock;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class WorkConversationMessageDTOFactoryTest extends TestCase
{
    use WorkApiDTOTraitMock;

    public function testCreate(): void
    {
        $statusType = new ConversationMessageStatusType;
        $statusType->setId(2);
        $statusType->setName('READ');
        $statusType->setDescription(null);

        $message = new ConversationMessage;
        $message->setId(826);
        $message->setContent('<p>Dobrý den</p>');
        $message->setOwner($this->createUser(36));
        $message->setRead(true);
        $message->setCreatedAt(new DateTimeImmutable('2017-04-02 07:45:22'));

        $status = new ConversationMessageStatus;
        $status->setId(5);
        $status->setType($statusType);
        $status->setUser($this->createUser(37));
        $status->setMessage($message);
        $message->setStatuses(new ArrayCollection([$status]));

        $dto = new WorkConversationMessageDTOFactory($this->createWorkDetailDTOFactory())->create($message);

        $this->assertSame(['id', 'read', 'content', 'owner', 'statuses', 'createdAt'], array_keys(get_object_vars($dto)));
        $this->assertSame(826, $dto->id);
        $this->assertTrue($dto->read);
        $this->assertSame('<p>Dobrý den</p>', $dto->content);
        $this->assertSame('2017-04-02 07:45:22', $dto->createdAt);
        $this->assertSame(36, $dto->owner->id);
        $this->assertCount(1, $dto->statuses);
        $this->assertSame(5, $dto->statuses[0]->id);
        $this->assertSame(['id' => 2, 'name' => 'READ', 'description' => null], get_object_vars($dto->statuses[0]->type));
        $this->assertSame(37, $dto->statuses[0]->user->id);
    }

    public function testMessageWithoutStatuses(): void
    {
        $message = new ConversationMessage;
        $message->setId(1);
        $message->setContent('text');
        $message->setOwner($this->createUser(36));
        $message->setRead(false);
        $message->setCreatedAt(new DateTimeImmutable('2020-01-01 00:00:00'));

        $dto = new WorkConversationMessageDTOFactory($this->createWorkDetailDTOFactory())->create($message);

        $this->assertSame([], $dto->statuses);
        $this->assertFalse($dto->read);
    }
}
