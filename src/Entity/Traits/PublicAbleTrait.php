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

use DateTime;
use Doctrine\Orm\Mapping as ORM;

trait PublicAbleTrait
{
    /**
     * @ORM\Column(name="public_from", type="datetime", nullable=true)
     */
    private ?DateTime $publicFrom = null;

    /**
     * @ORM\Column(name="public_to", type="datetime", nullable=true)
     */
    private ?DateTime $publicTo = null;

    public function getPublicFrom(): ?DateTime
    {
        return $this->publicFrom;
    }

    public function setPublicFrom(DateTime $publicFrom): void
    {
        $this->publicFrom = $publicFrom;
    }

    public function getPublicTo(): ?DateTime
    {
        return $this->publicTo;
    }

    public function setPublicTo(DateTime $publicTo): void
    {
        $this->publicTo = $publicTo;
    }
}
