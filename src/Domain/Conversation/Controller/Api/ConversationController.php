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

namespace App\Domain\Conversation\Controller\Api;

use App\Domain\Conversation\Http\Api\ConversationWorkMessageListHandle;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConversationController extends AbstractController
{
    public function __construct(private readonly ConversationWorkMessageListHandle $conversionWorkHandle) {}

    public function listWorkMessage(Request $request, Work $work): Response
    {
        return $this->conversionWorkHandle->handle($request, $work);
    }
}
