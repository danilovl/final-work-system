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

namespace FinalWork\FinalWorkBundle\Model\EventAddress;

use FinalWork\FinalWorkBundle\Entity\EventAddress;
use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};

class EventAddressFactory extends BaseModelFactory
{
    /**
     * @param EventAddressModel $addressModel
     * @param EventAddress|null $eventAddress
     * @return EventAddress
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flushFromModel(
        EventAddressModel $addressModel,
        EventAddress $eventAddress = null
    ): EventAddress {
        $eventAddress = $eventAddress ?? new EventAddress;
        $eventAddress = $this->fromModel($eventAddress, $addressModel);

        $this->em->persist($eventAddress);
        $this->em->flush();

        return $eventAddress;
    }

    /**
     * @param EventAddress $eventAddress
     * @param EventAddressModel $eventAddressModel
     * @return EventAddress
     */
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
