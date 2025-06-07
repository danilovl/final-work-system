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

namespace App\Domain\EmailNotification\Factory;

use App\Application\Factory\Model\BaseModelFactory;
use App\Domain\EmailNotification\Entity\EmailNotification;
use App\Domain\EmailNotification\Model\EmailNotificationModel;

class EmailNotificationFactory extends BaseModelFactory
{
    public function createFromModel(
        EmailNotificationModel $emailNotificationModel,
        ?EmailNotification $emailNotification = null
    ): EmailNotification {
        $emailNotification ??= new EmailNotification;
        $emailNotification = $this->fromModel($emailNotification, $emailNotificationModel);

        $this->entityManagerService->persistAndFlush($emailNotification);

        return $emailNotification;
    }

    public function fromModel(
        EmailNotification $emailNotification,
        EmailNotificationModel $emailNotificationModel
    ): EmailNotification {
        $emailNotification->setSubject($emailNotificationModel->subject);
        $emailNotification->setTo($emailNotificationModel->to);
        $emailNotification->setFrom($emailNotificationModel->from);
        $emailNotification->setBody($emailNotificationModel->body);
        $emailNotification->setSuccess($emailNotificationModel->success);
        $emailNotification->setUuid($emailNotificationModel->uuid);

        return $emailNotification;
    }
}
