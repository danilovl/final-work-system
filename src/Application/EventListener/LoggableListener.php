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

use App\Application\Messenger\Loggable\LoggableMessage;
use Danilovl\AsyncBundle\Service\AsyncService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Doctrine\Common\EventArgs;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Gedmo\Loggable\LogEntryInterface;
use Gedmo\Loggable\Mapping\Event\LoggableAdapter;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use Symfony\Component\Messenger\MessageBusInterface;

class LoggableListener extends \Gedmo\Loggable\LoggableListener
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly ParameterServiceInterface $parameterService,
        private readonly AsyncService $asyncService
    ) {
        parent::__construct();
    }

    public function postPersist(EventArgs $args): void {}

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

            if (LogEntryInterface::ACTION_CREATE === $action && $ea->isPostInsertGenerator($meta)) {
                $this->pendingLogEntryInserts[spl_object_id($object)] = $logEntry;
            } else {
                $logEntry->setObjectId($wrapped->getIdentifier());
            }
            $newValues = [];
            if (LogEntryInterface::ACTION_REMOVE !== $action && isset($config['versioned'])) {
                $newValues = $this->getObjectChangeSetData($ea, $object, $logEntry);
                $logEntry->setData($newValues);
            }

            if (LogEntryInterface::ACTION_UPDATE === $action && 0 === count($newValues)) {
                return null;
            }

            $version = 1;
            if (LogEntryInterface::ACTION_CREATE !== $action) {
                $version = $ea->getNewVersion($logEntryMeta, $object);
                if (empty($version)) {
                    $version = 1;
                }
            }
            $logEntry->setVersion($version);

            $this->prePersistLogEntry($logEntry, $object);

            $this->asyncService->add(function () use ($logEntry): void {
                $loggableMessage = new LoggableMessage($logEntry);
                $this->bus->dispatch($loggableMessage);
            });

            return null;
        }

        return null;
    }
}
