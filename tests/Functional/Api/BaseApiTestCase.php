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

namespace App\Tests\Functional\Api;

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\ApiUser\Entity\ApiUser;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Factory\ConversationFactory;
use App\Domain\ConversationType\Constant\ConversationTypeConstant;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\Security\Authenticator\ApiAuthenticator;
use App\Domain\User\Entity\User;
use App\Domain\Work\Bus\Command\DeleteWork\DeleteWorkCommand;
use App\Domain\Work\Entity\Work;
use App\Tests\Functional\Web\Enum\LoginData;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Index\IndexManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseApiTestCase extends WebTestCase
{
    protected const string SUPERVISOR = LoginData::SUPERVISOR_USERNAME->value;

    protected const string STUDENT = LoginData::STUDENT_USERNAME->value;

    private const array PASSWORDS = [
        LoginData::SUPERVISOR_USERNAME->value => LoginData::SUPERVISOR_PASSWORD->value,
        LoginData::STUDENT_USERNAME->value => LoginData::STUDENT_PASSWORD->value
    ];

    protected KernelBrowser $client;

    private string $apiKey;

    /** @var array<string, string> */
    private array $userTokens = [];

    /** @var array<string, int> */
    private array $userIds = [];

    /** @var int[] */
    private array $createdWorkIds = [];

    /** @var int[] */
    protected array $createdEventIds = [];

    private ?int $createdEventAddressId = null;

    /** @var int[] */
    private array $createdUserIds = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->apiKey = bin2hex(random_bytes(16));

        $apiUser = new ApiUser;
        $apiUser->setName('PHPUnit functional API');
        $apiUser->setApiKey($this->apiKey);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($apiUser);
        $entityManager->flush();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdEventIds as $eventId) {
            $uri = sprintf('/api/key/events/%d', $eventId);
            $this->requestApi(Request::METHOD_DELETE, $uri, self::SUPERVISOR);
        }

        $entityManager = $this->getEntityManager();
        $entityManager->clear();

        /** @var CommandBusInterface $commandBus */
        $commandBus = static::getContainer()->get(CommandBusInterface::class);

        foreach ($this->createdWorkIds as $workId) {
            $work = $entityManager->find(Work::class, $workId);
            if ($work === null) {
                continue;
            }

            $command = DeleteWorkCommand::create($work);
            $commandBus->dispatch($command);
            $entityManager->clear();
        }

        $apiUser = $entityManager
            ->getRepository(ApiUser::class)
            ->findOneBy(['apiKey' => $this->apiKey]);

        if ($apiUser !== null) {
            $entityManager->remove($apiUser);
        }

        if ($this->createdEventAddressId !== null) {
            $eventAddress = $entityManager->find(EventAddress::class, $this->createdEventAddressId);
            if ($eventAddress !== null) {
                $entityManager->remove($eventAddress);
            }
        }

        $entityManager->flush();

        foreach ($this->createdUserIds as $userId) {
            $user = $entityManager->find(User::class, $userId);
            if ($user !== null) {
                $entityManager->remove($user);
            }
        }

        $entityManager->flush();

        parent::tearDown();
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, string> $headers
     * @return array<mixed>
     */
    protected function requestApi(
        string $method,
        string $uri,
        ?string $username = null,
        ?array $body = null,
        array $headers = []
    ): array {
        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json'
        ];

        if ($username !== null) {
            $authHeaders = $this->getAuthHeaders($username);
            $server = array_merge($server, $authHeaders);
        }

        $server = array_merge($server, $headers);
        $content = $body !== null ? json_encode($body, JSON_THROW_ON_ERROR) : null;

        $this->client->request(
            method: $method,
            uri: $uri,
            server: $server,
            content: $content
        );

        return $this->getResponseData();
    }

    /**
     * @return array<mixed>
     */
    protected function getResponseData(): array
    {
        $content = (string) $this->client->getResponse()->getContent();
        if ($content === '') {
            return [];
        }

        $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($data);

        return $data;
    }

    /**
     * @return array<string, string>
     */
    protected function getAuthHeaders(string $username): array
    {
        if (!isset($this->userTokens[$username])) {
            $this->userTokens[$username] = $this->generateToken($username);
        }

        return [
            $this->toServerKey(ApiAuthenticator::AUTH_KEY) => $this->apiKey,
            $this->toServerKey(ApiAuthenticator::AUTH_USER_TOKEN_KEY) => $this->userTokens[$username],
            $this->toServerKey(ApiAuthenticator::AUTH_USER_USERNAME) => $username
        ];
    }

    protected function getApiKey(): string
    {
        return $this->apiKey;
    }

    protected function toServerKey(string $header): string
    {
        $header = mb_strtoupper($header);

        return 'HTTP_' . str_replace('-', '_', $header);
    }

    protected function getUserId(string $username): int
    {
        if (!isset($this->userIds[$username])) {
            $data = $this->requestApi(Request::METHOD_GET, '/api/key/users/detail', $username);
            $this->assertResponseStatusCodeSame(200);

            $this->userIds[$username] = $this->getInt($data, 'id');
        }

        return $this->userIds[$username];
    }

    /**
     * @return array<mixed>
     */
    protected function createWork(string $title, ?int $authorId = null): array
    {
        $data = $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/key/works/create',
            username: self::SUPERVISOR,
            body: [
                'title' => $title,
                'shortcut' => 'API',
                'statusId' => 1,
                'typeId' => 1,
                'authorId' => $authorId ?? $this->getUserId(self::STUDENT),
                'opponentId' => null,
                'consultantId' => null,
                'deadline' => '2035-06-30',
                'deadlineProgram' => null,
                'categoriesIds' => []
            ]
        );

        $this->assertResponseStatusCodeSame(201);

        $work = $this->getArray($data, 'result');
        $this->createdWorkIds[] = $this->getInt($work, 'id');

        return $work;
    }

    protected function createStudent(): int
    {
        $username = 'api-test-' . bin2hex(random_bytes(4));
        $email = $username . '@example.com';

        $student = new User;
        $student->setUsername($username);
        $student->setUsernameCanonical($username);
        $student->setEmail($email);
        $student->setEmailCanonical($email);
        $student->setFirstname('API');
        $student->setLastname($username);
        $student->setGender('u');
        $student->setRoles(['ROLE_STUDENT']);
        $student->setPassword('!');
        $student->setEnabled(true);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($student);
        $entityManager->flush();

        $studentId = $student->getId();
        $this->createdUserIds[] = $studentId;

        return $studentId;
    }

    protected function createWorkConversation(int $workId, bool $withStudent = true): int
    {
        $supervisorId = $this->getUserId(self::SUPERVISOR);
        $studentId = $this->getUserId(self::STUDENT);
        $entityManager = $this->getEntityManager();

        $work = $entityManager->find(Work::class, $workId);
        $this->assertNotNull($work);

        $supervisor = $entityManager->find(User::class, $supervisorId);
        $student = $entityManager->find(User::class, $studentId);
        $this->assertNotNull($supervisor);
        $this->assertNotNull($student);

        /** @var ConversationFactory $conversationFactory */
        $conversationFactory = static::getContainer()->get(ConversationFactory::class);

        $conversation = $conversationFactory->createConversation(
            owner: $supervisor,
            type: ConversationTypeConstant::WORK->value,
            work: $work,
            name: $work->getTitle()
        );

        $conversationFactory->createConversationParticipant(
            conversation: $conversation,
            participants: $withStudent ? [$supervisor, $student] : [$supervisor]
        );

        $entityManager->flush();
        $this->assertInstanceOf(Conversation::class, $conversation);

        return $conversation->getId();
    }

    protected function getEventAddressId(): int
    {
        $supervisorId = $this->getUserId(self::SUPERVISOR);
        $entityManager = $this->getEntityManager();
        $supervisor = $entityManager->find(User::class, $supervisorId);
        $this->assertNotNull($supervisor);

        /** @var EventAddress|null $eventAddress */
        $eventAddress = $entityManager
            ->getRepository(EventAddress::class)
            ->findOneBy(['owner' => $supervisor]);

        if ($eventAddress === null) {
            $eventAddress = new EventAddress;
            $eventAddress->setName('API test address');
            $eventAddress->setOwner($supervisor);
            $eventAddress->setSkype(false);

            $entityManager->persist($eventAddress);
            $entityManager->flush();

            $this->createdEventAddressId = $eventAddress->getId();
        }

        return $eventAddress->getId();
    }

    protected function refreshSearchIndexes(): void
    {
        /** @var IndexManager $indexManager */
        $indexManager = static::getContainer()->get(IndexManager::class);

        foreach ($indexManager->getAllIndexes() as $index) {
            $index->refresh();
        }
    }

    protected function uniqueName(string $prefix): string
    {
        $suffix = bin2hex(random_bytes(4));

        return $prefix . ' ' . $suffix;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        return $entityManager;
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    protected function getArray(array $data, string|int ...$path): array
    {
        $value = $this->getValue($data, ...$path);
        $this->assertIsArray($value);

        return $value;
    }

    /**
     * @param array<mixed> $data
     * @return list<array<mixed>>
     */
    protected function getList(array $data, string|int ...$path): array
    {
        $value = $path === [] ? $data : $this->getValue($data, ...$path);
        $this->assertIsList($value);

        $items = [];
        foreach ($value as $item) {
            $this->assertIsArray($item);
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param array<mixed> $data
     */
    protected function getInt(array $data, string|int ...$path): int
    {
        $value = $this->getValue($data, ...$path);
        $this->assertIsInt($value);

        return $value;
    }

    /**
     * @param array<mixed> $data
     */
    protected function getString(array $data, string|int ...$path): string
    {
        $value = $this->getValue($data, ...$path);
        $this->assertIsString($value);

        return $value;
    }

    /**
     * @param array<mixed> $data
     */
    protected function getBool(array $data, string|int ...$path): bool
    {
        $value = $this->getValue($data, ...$path);
        $this->assertIsBool($value);

        return $value;
    }

    /**
     * @param array<mixed> $items
     * @return int[]
     */
    protected function getIds(array $items, string|int ...$path): array
    {
        $ids = [];
        foreach ($this->getList($items, ...$path) as $item) {
            $ids[] = $this->getInt($item, 'id');
        }

        return $ids;
    }

    /**
     * @param array<mixed> $data
     */
    private function getValue(array $data, string|int ...$path): mixed
    {
        $value = $data;

        foreach ($path as $key) {
            $this->assertIsArray($value);
            $this->assertArrayHasKey($key, $value);

            $value = $value[$key];
        }

        return $value;
    }

    private function generateToken(string $username): string
    {
        $data = $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/public/security/generate-token',
            body: [
                'username' => $username,
                'password' => self::PASSWORDS[$username]
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        return $this->getString($data, 'token');
    }
}
