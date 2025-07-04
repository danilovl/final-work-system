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

namespace App\Tests\Integration\Domain\ConversationMessage\Command;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Command\DecodeConversationMessageCommand;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationType\Constant\ConversationTypeConstant;
use App\Domain\ConversationType\Entity\ConversationType;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Infrastructure\Service\EntityManagerService;
use Doctrine\Common\Collections\Order;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\LazyCommand;
use Symfony\Component\Console\Tester\CommandTester;

class DecodeConversationMessageCommandTest extends KernelTestCase
{
    private LazyCommand $command;

    private EntityManagerService $entityManagerService;

    private string $messageEncode = '&lt;a href=&quot;https://www.w3schools.com&quot;&gt;w3schools.com&lt;/a&gt;';

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        /** @var LazyCommand $command */
        $command = $application->find(DecodeConversationMessageCommand::COMMAND_NAME);

        $this->command = $command;
        $this->entityManagerService = $kernel->getContainer()->get(EntityManagerService::class);
    }

    public function testExecute(): array
    {
        $data = $this->prepareData();

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        return $data;
    }

    #[Depends('testExecute')]
    public function testContent(array $data): void
    {
        [$conversationId, $conversationMessageId] = $data;

        /** @var ConversationMessage $message */
        $message = $this->entityManagerService
            ->getRepository(ConversationMessage::class)
            ->find($conversationMessageId);

        $this->entityManagerService->refresh($message);

        $this->assertEquals(
            html_entity_decode($this->messageEncode),
            $message->getContent()
        );

        $this->removeTestConversation($conversationId);
    }

    private function prepareData(): array
    {
        /** @var ConversationType $conversationType */
        $conversationType = $this->entityManagerService->getReference(
            ConversationType::class,
            ConversationTypeConstant::WORK->value
        );

        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManagerService->getRepository(User::class);
        /** @var User $user */
        $user = $userRepository->findOneBy([], ['id' => Order::Ascending->value]);

        $conversation = new Conversation;
        $conversation->setName('kernel test');
        $conversation->setOwner($user);
        $conversation->setType($conversationType);

        $this->entityManagerService->persistAndFlush($conversation);

        $message = new ConversationMessage;
        $message->setOwner($user);
        $message->setConversation($conversation);
        $message->setContent($this->messageEncode);

        $this->entityManagerService->persistAndFlush($message);

        return [$conversation->getId(), $message->getId()];
    }

    private function removeTestConversation(int $conversationId): void
    {
        /** @var Conversation $conversation */
        $conversation = $this->entityManagerService
            ->getRepository(Conversation::class)
            ->find($conversationId);

        $this->entityManagerService->remove($conversation);
    }
}
