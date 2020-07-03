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

trait UserInformationTrait
{
    /**
     * @ORM\Column(name="degree_before", type="string", nullable=true))
     */
    private ?string $degreeBefore = null;

    /**
     * @ORM\Column(name="first_name", type="string", nullable=false))
     */
    private ?string $firstName = null;

    /**
     * @ORM\Column(name="second_name", type="string", type="string", nullable=false))
     */
    private ?string$secondName = null;

    /**
     * @ORM\Column(name="degree_after", type="string", nullable=true))
     */
    private ?string$degreeAfter = null;

    public function getDegreeBefore(): ?string
    {
        return $this->degreeBefore;
    }

    public function setDegreeBefore(string $degreeBefore): void
    {
        $this->degreeBefore = $degreeBefore;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getSecondName(): ?string
    {
        return $this->secondName;
    }

    public function setSecondName(string $secondName): void
    {
        $this->secondName = $secondName;
    }

    public function getDegreeAfter(): ?string
    {
        return $this->degreeAfter;
    }

    public function setDegreeAfter(?string $degreeAfter): void
    {
        $this->degreeAfter = $degreeAfter;
    }
}
