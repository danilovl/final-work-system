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

namespace App\Domain\EventAddress\Model;

use App\Application\Traits\Model\{
    SimpleInformationTrait};
use App\Application\Traits\Model\LocationTrait;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\User\Entity\User;

class EventAddressModel
{
    use SimpleInformationTrait;
    use LocationTrait;

    public bool $skype = false;
    public ?string $street = null;
    public ?User $owner = null;

    public static function fromEventAddress(EventAddress $eventAddress): self
    {
        $model = new self;
        $model->name = $eventAddress->getName();
        $model->description = $eventAddress->getDescription();
        $model->street = $eventAddress->getStreet();
        $model->latitude = $eventAddress->getLatitude();
        $model->longitude = $eventAddress->getLongitude();
        $model->skype = $eventAddress->isSkype();
        $model->owner = $eventAddress->getOwner();

        return $model;
    }
}