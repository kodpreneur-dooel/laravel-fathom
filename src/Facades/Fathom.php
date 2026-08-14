<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Facades;

use Codepreneur\Fathom\Resources\MeetingsResource;
use Codepreneur\Fathom\Resources\RecordingsResource;
use Codepreneur\Fathom\Resources\TeamMembersResource;
use Codepreneur\Fathom\Resources\TeamsResource;
use Codepreneur\Fathom\Resources\UsersResource;
use Codepreneur\Fathom\Resources\WebhooksResource;
use Illuminate\Support\Facades\Facade;

/**
 * @method static MeetingsResource meetings()
 * @method static RecordingsResource recordings()
 * @method static TeamsResource teams()
 * @method static TeamMembersResource teamMembers()
 * @method static UsersResource users()
 * @method static WebhooksResource webhooks()
 *
 * @see \Codepreneur\Fathom\Fathom
 */
class Fathom extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'fathom';
    }
}
