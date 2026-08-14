<?php

declare(strict_types=1);

namespace Codepreneur\Fathom;

use Codepreneur\Fathom\Resources\MeetingsResource;
use Codepreneur\Fathom\Resources\RecordingsResource;
use Codepreneur\Fathom\Resources\TeamMembersResource;
use Codepreneur\Fathom\Resources\TeamsResource;
use Codepreneur\Fathom\Resources\UsersResource;
use Codepreneur\Fathom\Resources\WebhooksResource;

class Fathom
{
    public function __construct(
        protected FathomClient $client,
        protected ?string $webhookSecret = null,
        protected int $webhookTolerance = 300,
    ) {}

    public function meetings(): MeetingsResource
    {
        return new MeetingsResource($this->client);
    }

    public function recordings(): RecordingsResource
    {
        return new RecordingsResource($this->client);
    }

    public function teams(): TeamsResource
    {
        return new TeamsResource($this->client);
    }

    public function teamMembers(): TeamMembersResource
    {
        return new TeamMembersResource($this->client);
    }

    public function users(): UsersResource
    {
        return new UsersResource($this->client);
    }

    public function webhooks(): WebhooksResource
    {
        return new WebhooksResource(
            $this->client,
            $this->webhookSecret,
            $this->webhookTolerance
        );
    }

    public function client(): FathomClient
    {
        return $this->client;
    }
}
