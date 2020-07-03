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

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait LocationTrait
{
    /**
     * @ORM\Column(name="latitude", type="decimal", precision=9, scale=7, nullable=true)
     */
    private ?float $latitude = null;

    /**
     * @ORM\Column(name="longitude", type="decimal", precision=10, scale=7, nullable=true)
     */
    private ?float $longitude = null;

    public function getLatitude(): ?float
    {
        return (float)$this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = (float)$latitude;
    }

    public function getLongitude(): ?float
    {
        return (float)$this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = (float)$longitude;
    }
}
