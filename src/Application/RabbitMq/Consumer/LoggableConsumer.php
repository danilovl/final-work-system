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

namespace App\Application\RabbitMq\Consumer;

use App\Application\Helper\SerializerHelper;
use App\Application\Service\EntityManagerService;
use Gedmo\Loggable\Entity\LogEntry;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Serializer\Serializer;

class LoggableConsumer implements ConsumerInterface
{
    private Serializer $serializer;

    public function __construct(private readonly EntityManagerService $entityManagerService)
    {
        $this->serializer = SerializerHelper::getBaseSerializer();
    }

    public function execute(AMQPMessage $msg): int
    {
        /** @var LogEntry $logEntry */
        $logEntry = $this->serializer->deserialize($msg->getBody(), LogEntry::class, 'json');
        $logEntry->setLoggedAt();

        $this->entityManagerService->persistAndFlush($logEntry);

        echo sprintf('Success create log for class "%s" and username "%s". %s', $logEntry->getObjectClass(), $logEntry->getUsername(), PHP_EOL);

        return ConsumerInterface::MSG_ACK;
    }
}
