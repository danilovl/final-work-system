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

namespace App\Model\EmailNotificationQueue;

use App\Entity\EmailNotificationQueue;
use App\Model\BaseModelFactory;

class EmailNotificationQueueFactory extends BaseModelFactory
{
    public function createFromModel(
        EmailNotificationQueueModel $emailNotificationQueueModel,
        EmailNotificationQueue $emailNotificationQueue = null
    ): EmailNotificationQueue {
        $emailNotificationQueue = $emailNotificationQueue ?? new EmailNotificationQueue;
        $emailNotificationQueue = $this->fromModel($emailNotificationQueue, $emailNotificationQueueModel);

        $this->entityManagerService->persistAndFlush($emailNotificationQueue);

        return $emailNotificationQueue;
    }

    public function fromModel(
        EmailNotificationQueue $emailNotificationQueue,
        EmailNotificationQueueModel $notificationQueueModel
    ): EmailNotificationQueue {
        $emailNotificationQueue->setSubject($notificationQueueModel->subject);
        $emailNotificationQueue->setTo($notificationQueueModel->to);
        $emailNotificationQueue->setFrom($notificationQueueModel->from);
        $emailNotificationQueue->setBody($notificationQueueModel->body);
        $emailNotificationQueue->setSuccess($notificationQueueModel->success);

        return $emailNotificationQueue;
    }
}
