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

namespace App\Tests\Functional\Api\Domain\Conversation\Controller\Api;

use App\Tests\Functional\Api\BaseApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class ConversationControllerTest extends BaseApiTestCase
{
    public function testConversationWork(): void
    {
        [$workId, $conversationId] = $this->createConversation();
        $uri = sprintf('/api/key/conversations/works/%d', $workId);

        $data = $this->requestApi(Request::METHOD_GET, $uri, self::STUDENT);

        $this->assertResponseStatusCodeSame(200);

        $id = $this->getInt($data, 'id');
        $this->assertSame($conversationId, $id);
    }

    public function testConversationWorkWithoutConversationNotFound(): void
    {
        $work = $this->createWork($this->uniqueName('API no conversation'));
        $workId = $this->getInt($work, 'id');
        $uri = sprintf('/api/key/conversations/works/%d', $workId);

        $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDetail(): void
    {
        [, $conversationId, $title] = $this->createConversation();
        $uri = sprintf('/api/key/conversations/%d', $conversationId);

        $data = $this->requestApi(Request::METHOD_GET, $uri, self::STUDENT);

        $this->assertResponseStatusCodeSame(200);

        $id = $this->getInt($data, 'id');
        $name = $this->getString($data, 'name');

        $this->assertSame($conversationId, $id);
        $this->assertSame($title, $name);
    }

    public function testDetailForbiddenForNotParticipant(): void
    {
        $work = $this->createWork($this->uniqueName('API private conversation'));
        $workId = $this->getInt($work, 'id');
        $conversationId = $this->createWorkConversation($workId, false);

        $detailUri = sprintf('/api/key/conversations/%d', $conversationId);
        $messagesUri = sprintf('/api/key/conversations/%d/messages', $conversationId);
        $createUri = sprintf('/api/key/conversations/%d/message', $conversationId);

        $this->requestApi(Request::METHOD_GET, $detailUri, self::STUDENT);
        $this->assertResponseStatusCodeSame(403);

        $this->requestApi(Request::METHOD_GET, $messagesUri, self::STUDENT);
        $this->assertResponseStatusCodeSame(403);

        $this->requestApi(
            method: Request::METHOD_POST,
            uri: $createUri,
            username: self::STUDENT,
            body: ['message' => 'Not allowed']
        );
        $this->assertResponseStatusCodeSame(403);
    }

    public function testDetailNotFound(): void
    {
        $this->requestApi(Request::METHOD_GET, '/api/key/conversations/999999999', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateMessageAndList(): void
    {
        [$workId, $conversationId] = $this->createConversation();
        $content = $this->uniqueName('API conversation message');

        $this->createMessage($conversationId, $content);

        $uri = sprintf('/api/key/conversations/%d/messages', $conversationId);
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::STUDENT);

        $this->assertResponseStatusCodeSame(200);

        $totalCount = $this->getInt($data, 'totalCount');
        $messageContent = $this->getString($data, 'result', 0, 'content');
        $ownerId = $this->getInt($data, 'result', 0, 'owner', 'id');
        $isRead = $this->getBool($data, 'result', 0, 'isRead');

        $this->assertSame(1, $totalCount);
        $this->assertSame($content, $messageContent);
        $this->assertSame($this->getUserId(self::SUPERVISOR), $ownerId);
        $this->assertFalse($isRead);

        $uri = sprintf('/api/key/conversations/works/%d/messages', $workId);
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::STUDENT);

        $this->assertResponseStatusCodeSame(200);

        $workMessageContent = $this->getString($data, 'result', 0, 'content');
        $this->assertSame($content, $workMessageContent);
    }

    public function testCreateMessageValidationFailed(): void
    {
        [, $conversationId] = $this->createConversation();
        $uri = sprintf('/api/key/conversations/%d/message', $conversationId);

        $data = $this->requestApi(
            method: Request::METHOD_POST,
            uri: $uri,
            username: self::SUPERVISOR,
            body: ['message' => '']
        );

        $this->assertResponseStatusCodeSame(400);

        $property = $this->getString($data, 'errors', 0, 'property');
        $this->assertSame('message', $property);
    }

    public function testMessagesSearch(): void
    {
        [, $conversationId] = $this->createConversation();
        $content = $this->uniqueName('API searchable message');
        $this->createMessage($conversationId, 'API other message');
        $this->createMessage($conversationId, $content);
        $this->refreshSearchIndexes();

        $suffix = mb_substr($content, -6);
        $search = mb_strtoupper($suffix);
        $uri = sprintf('/api/key/conversations/%d/messages?search=%s', $conversationId, rawurlencode($search));
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::STUDENT);

        $this->assertResponseStatusCodeSame(200);

        $totalCount = $this->getInt($data, 'totalCount');
        $messageContent = $this->getString($data, 'result', 0, 'content');

        $this->assertSame(1, $totalCount);
        $this->assertSame($content, $messageContent);
    }

    public function testChangeMessageReadStatus(): void
    {
        [, $conversationId] = $this->createConversation();
        $this->createMessage($conversationId, 'API read status message');

        $messagesUri = sprintf('/api/key/conversations/%d/messages', $conversationId);
        $data = $this->requestApi(Request::METHOD_GET, $messagesUri, self::STUDENT);
        $messageId = $this->getInt($data, 'result', 0, 'id');
        $statusUri = sprintf('/api/key/conversations/%d/messages/%d/change/read/status', $conversationId, $messageId);

        $this->requestApi(Request::METHOD_PUT, $statusUri, self::STUDENT);
        $this->assertResponseStatusCodeSame(200);

        $data = $this->requestApi(Request::METHOD_GET, $messagesUri, self::STUDENT);
        $isRead = $this->getBool($data, 'result', 0, 'isRead');
        $this->assertTrue($isRead);

        $this->requestApi(Request::METHOD_PUT, $statusUri, self::STUDENT);
        $this->assertResponseStatusCodeSame(200);

        $data = $this->requestApi(Request::METHOD_GET, $messagesUri, self::STUDENT);
        $isRead = $this->getBool($data, 'result', 0, 'isRead');
        $this->assertFalse($isRead);
    }

    public function testChangeMessageReadStatusMessageFromAnotherConversation(): void
    {
        [, $conversationId] = $this->createConversation();
        [, $anotherConversationId] = $this->createConversation();
        $this->createMessage($conversationId, 'API foreign message');

        $messagesUri = sprintf('/api/key/conversations/%d/messages', $conversationId);
        $data = $this->requestApi(Request::METHOD_GET, $messagesUri, self::STUDENT);
        $messageId = $this->getInt($data, 'result', 0, 'id');

        $statusUri = sprintf('/api/key/conversations/%d/messages/%d/change/read/status', $anotherConversationId, $messageId);
        $this->requestApi(Request::METHOD_PUT, $statusUri, self::STUDENT);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testChangeAllMessageToRead(): void
    {
        [, $conversationId] = $this->createConversation();
        $this->createMessage($conversationId, 'API unread first');
        $this->createMessage($conversationId, 'API unread second');

        $this->requestApi(Request::METHOD_PUT, '/api/key/conversations/messages/change-all-to-read', self::STUDENT);
        $this->assertResponseStatusCodeSame(200);

        $uri = sprintf('/api/key/conversations/%d/messages', $conversationId);
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::STUDENT);
        $messages = $this->getList($data, 'result');
        $unreadMessages = array_filter($messages, fn (array $message): bool => !$this->getBool($message, 'isRead'));

        $this->assertCount(2, $messages);
        $this->assertSame([], $unreadMessages);
    }

    public function testList(): void
    {
        [, $conversationId, $title] = $this->createConversation();
        $this->createMessage($conversationId, 'API list message');
        $this->refreshSearchIndexes();

        $data = $this->requestApi(Request::METHOD_GET, '/api/key/conversations/?limit=1000', self::STUDENT);

        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $totalCount = $this->getInt($data, 'totalCount');
        $this->assertContains($conversationId, $ids);
        $this->assertGreaterThanOrEqual(1, $totalCount);

        $conversation = array_find($this->getList($data, 'result'), fn (array $item): bool => $this->getInt($item, 'id') === $conversationId);
        $this->assertIsArray($conversation);

        $isRead = $this->getBool($conversation, 'isRead');
        $recipientId = $this->getInt($conversation, 'recipient', 'id');

        $this->assertFalse($isRead);
        $this->assertSame($this->getUserId(self::SUPERVISOR), $recipientId);

        $suffix = mb_substr($title, -6);
        $uri = sprintf('/api/key/conversations/?search=%s', rawurlencode($suffix));
        $data = $this->requestApi(Request::METHOD_GET, $uri, self::STUDENT);

        $this->assertResponseStatusCodeSame(200);

        $ids = $this->getIds($data, 'result');
        $this->assertSame([$conversationId], $ids);
    }

    public function testListSearchNothingFound(): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/conversations/?search=zzz-no-conversation-zzz', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(200);

        $totalCount = $this->getInt($data, 'totalCount');
        $this->assertSame(0, $totalCount);
    }

    /**
     * @return array{int, int, string}
     */
    private function createConversation(): array
    {
        $title = $this->uniqueName('API conversation work');
        $work = $this->createWork($title);
        $workId = $this->getInt($work, 'id');
        $conversationId = $this->createWorkConversation($workId);

        return [$workId, $conversationId, $title];
    }

    private function createMessage(int $conversationId, string $content): void
    {
        $uri = sprintf('/api/key/conversations/%d/message', $conversationId);

        $this->requestApi(
            method: Request::METHOD_POST,
            uri: $uri,
            username: self::SUPERVISOR,
            body: ['message' => $content]
        );

        $this->assertResponseStatusCodeSame(201);
    }
}
