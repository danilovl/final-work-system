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

namespace App\Infrastructure\Event;

class InfrastructureEvents
{
    public const string CACHE_CLEAR_KEY = 'cache.clear.key';

    public const string ENTITY_CREATE = 'entity.create';
    public const string ENTITY_CREATE_ASYNC = 'entity.create.async';
    public const string ENTITY_REMOVE = 'entity.remove';
    public const string ENTITY_SAVE = 'entity.save';

    public const string REQUEST_FLASH_CREATE_FAILURE = 'request.flash.create.failure';
    public const string REQUEST_FLASH_SAVE_FAILURE = 'request.flash.save.failure';
    public const string REQUEST_FLASH_DELETE_FAILURE = 'request.flash.delete.failure';
}
