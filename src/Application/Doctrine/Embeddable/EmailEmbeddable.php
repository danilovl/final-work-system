<?php declare(strict_types=1);

namespace App\Application\Doctrine\Embeddable;

use App\Application\Exception\InvalidArgumentException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\{
    Column,
    Embeddable
};
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use Stringable;

#[Embeddable]
class EmailEmbeddable implements Stringable
{
    #[Column(name: 'email', type: Types::STRING, unique: true, nullable: false)]
    private string $email;

    public function __construct(string $email)
    {
        $this->setEmail($email);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    private function setEmail(string $email): void
    {
        $email = trim($email);

        $validator = new EmailValidator;

        if (!$validator->isValid($email, new NoRFCWarningsValidation)) {
            throw new InvalidArgumentException;
        }

        $this->email = $email;
    }

    public function toString(): string
    {
        return $this->email;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
