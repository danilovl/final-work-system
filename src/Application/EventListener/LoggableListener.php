<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\EventListener;

use App\Application\Helper\SerializerHelper;
use Danilovl\AsyncBundle\Service\AsyncService;
use Danilovl\ParameterBundle\Service\ParameterService;
use Doctrine\Common\EventArgs;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Gedmo\Loggable\Mapping\Event\LoggableAdapter;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\Serializer\Serializer;

class LoggableListener extends \Gedmo\Loggable\LoggableListener
{
    private Serializer $serializer;

    public function __construct(
        protected ProducerInterface $loggableProducer,
        private ParameterService $parameterService,
        private AsyncService $asyncService
    ) {
        parent::__construct();

        $this->serializer = SerializerHelper::getBaseSerializer();
    }

    public function postPersist(EventArgs $args): void
    {
    }

    protected function createLogEntry($action, $object, LoggableAdapter $ea): ?AbstractLogEntry
    {
        $isEnable = $this->parameterService->getBoolean('loggable.enable');
        if (!$isEnable) {
            return null;
        }

        $om = $ea->getObjectManager();
        $wrapped = AbstractWrapper::wrap($object, $om);
        $meta = $wrapped->getMetadata();

        if (isset($meta->isEmbeddedDocument) && $meta->isEmbeddedDocument) {
            return null;
        }

        if ($config = $this->getConfiguration($om, $meta->getName())) {
            $logEntryClass = $this->getLogEntryClass($ea, $meta->getName());
            $logEntryMeta = $om->getClassMetadata($logEntryClass);
            /** @var LogEntry $logEntry */
            $logEntry = $logEntryMeta->newInstance();

            $logEntry->setAction($action);
            $logEntry->setUsername($this->username);
            $logEntry->setObjectClass($meta->getName());
            $logEntry->setLoggedAt();

            if (self::ACTION_CREATE === $action && $ea->isPostInsertGenerator($meta)) {
                $this->pendingLogEntryInserts[spl_object_id($object)] = $logEntry;
            } else {
                $logEntry->setObjectId($wrapped->getIdentifier());
            }
            $newValues = [];
            if (self::ACTION_REMOVE !== $action && isset($config['versioned'])) {
                $newValues = $this->getObjectChangeSetData($ea, $object, $logEntry);
                $logEntry->setData($newValues);
            }

            if (self::ACTION_UPDATE === $action && 0 === count($newValues)) {
                return null;
            }

            $version = 1;
            if (self::ACTION_CREATE !== $action) {
                $version = $ea->getNewVersion($logEntryMeta, $object);
                if (empty($version)) {
                    $version = 1;
                }
            }
            $logEntry->setVersion($version);

            $this->prePersistLogEntry($logEntry, $object);

            $this->asyncService->add(function () use ($logEntry): void {
                $msgBody = $this->serializer->serialize($logEntry, 'json');
                $this->loggableProducer->publish($msgBody);
            });

            return null;
        }

        return null;
    }
}