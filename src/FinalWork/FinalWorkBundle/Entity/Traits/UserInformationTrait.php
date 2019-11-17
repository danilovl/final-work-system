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

use Doctrine\ORM\Mapping as ORM;

trait UserInformationTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="degree_before", type="string", nullable=true))
     */
    private $degreeBefore;

    /**
     * @var string|null
     *
     * @ORM\Column(name="first_name", type="string", nullable=false))
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="second_name", type="string", type="string", nullable=false))
     */
    private $secondName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="degree_after", type="string", nullable=true))
     */
    private $degreeAfter;

    /**
     * @return string|null
     */
    public function getDegreeBefore(): ?string
    {
        return $this->degreeBefore;
    }

    /**
     * @param string|null $degreeBefore
     */
    public function setDegreeBefore(string $degreeBefore): void
    {
        $this->degreeBefore = $degreeBefore;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     */
    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getSecondName(): ?string
    {
        return $this->secondName;
    }

    /**
     * @param string $secondName
     */
    public function setSecondName(string $secondName): void
    {
        $this->secondName = $secondName;
    }

    /**
     * @return string|null
     */
    public function getDegreeAfter(): ?string
    {
        return $this->degreeAfter;
    }

    /**
     * @param string|null $degreeAfter
     */
    public function setDegreeAfter(?string $degreeAfter): void
    {
        $this->degreeAfter = $degreeAfter;
    }
}
