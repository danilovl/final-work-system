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

namespace App\Domain\Task\Http\Api;

use App\Application\Helper\SerializerHelper;
use App\Domain\Task\DTO\Api\Output\TaskDetailOutput;
use App\Domain\Task\DTO\Api\TaskDTO;
use App\Domain\Task\Entity\Task;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

readonly class TaskDetailHandle
{
    public function __invoke(Task $task): TaskDetailOutput
    {
        $serializeContext [AbstractNormalizer::IGNORED_ATTRIBUTES] = ['owner', 'work', 'systemEvents'];

        /** @var TaskDTO $result */
        $result = SerializerHelper::convertToObject($task, TaskDTO::class, $serializeContext);

        return new TaskDetailOutput($result);
    }
}
