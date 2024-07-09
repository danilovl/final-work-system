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

namespace App\Application\Traits\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait LocationTrait
{
    #[ORM\Column(name: 'latitude', type: Types::DECIMAL, precision: 9, scale: 7, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(name: 'longitude', type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $longitude = null;

    public function getLatitude(): ?float
    {
        return !empty($this->latitude) ? (float) $this->latitude : null;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = (string) $latitude;
    }

    public function getLongitude(): ?float
    {
        return !empty($this->longitude) ? (float) $this->longitude : null;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = (string) $longitude;
    }
}
