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

namespace FinalWork\FinalWorkBundle\Entity\Traits;

use DateTime;
use Doctrine\Orm\Mapping as ORM;

trait PublicAbleTrait
{
    /**
     * @var DateTime
     *
     * @ORM\Column(name="public_from", type="datetime", nullable=true)
     */
    private $publicFrom;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="public_to", type="datetime", nullable=true)
     */
    private $publicTo;

    /**
     * @return DateTime
     */
    public function getPublicFrom(): ?DateTime
    {
        return $this->publicFrom;
    }

    /**
     * @param DateTime $publicFrom
     */
    public function setPublicFrom(DateTime $publicFrom): void
    {
        $this->publicFrom = $publicFrom;
    }

    /**
     * @return DateTime
     */
    public function getPublicTo(): ?DateTime
    {
        return $this->publicTo;
    }

    /**
     * @param DateTime $publicTo
     */
    public function setPublicTo(DateTime $publicTo): void
    {
        $this->publicTo = $publicTo;
    }
}
