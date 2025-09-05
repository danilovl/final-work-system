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

namespace App\Domain\Work\Bus\Command\EditAuthor;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Factory\UserFactory;

readonly class EditAuthorHandler implements CommandHandlerInterface
{
    public function __construct(private UserFactory $userFactory,) {}

    public function __invoke(EditAuthorCommand $command): User
    {
        return $this->userFactory->flushFromModel($command->userModel, $command->user);
    }
}
