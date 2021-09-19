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

namespace App\Model\EventAddress\Factory;

use App\Entity\EventAddress;
use App\Model\BaseModelFactory;
use App\Model\EventAddress\EventAddressModel;

class EventAddressFactory extends BaseModelFactory
{
    public function flushFromModel(
        EventAddressModel $addressModel,
        EventAddress $eventAddress = null
    ): EventAddress {
        $eventAddress = $eventAddress ?? new EventAddress;
        $eventAddress = $this->fromModel($eventAddress, $addressModel);

        $this->entityManagerService->persistAndFlush($eventAddress);

        return $eventAddress;
    }

    public function fromModel(
        EventAddress $eventAddress,
        EventAddressModel $eventAddressModel
    ): EventAddress {
        $eventAddress->setName($eventAddressModel->name);
        $eventAddress->setDescription($eventAddressModel->description);
        $eventAddress->setStreet($eventAddressModel->street);
        $eventAddress->setLatitude($eventAddressModel->latitude);
        $eventAddress->setLongitude($eventAddressModel->longitude);
        $eventAddress->setOwner($eventAddressModel->owner);

        return $eventAddress;
    }
}
