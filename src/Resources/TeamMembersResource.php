<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Resources;

use Codepreneur\Fathom\Data\PaginatedResponse;
use Codepreneur\Fathom\Data\TeamMemberData;
use Codepreneur\Fathom\FathomClient;

class TeamMembersResource
{
    public function __construct(
        protected FathomClient $client,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return PaginatedResponse<TeamMemberData>
     */
    public function list(array $filters = []): PaginatedResponse
    {
        $data = $this->client->get('team_members', $filters);

        return PaginatedResponse::fromArray($data, TeamMemberData::fromArray(...));
    }

    /**
     * @param  PaginatedResponse<TeamMemberData>  $page
     * @param  array<string, mixed>  $filters
     * @return PaginatedResponse<TeamMemberData>
     */
    public function next(PaginatedResponse $page, array $filters = []): PaginatedResponse
    {
        if (! $page->hasMore()) {
            return new PaginatedResponse(
                limit: $page->limit,
                nextCursor: null,
                items: [],
            );
        }

        return $this->list(array_merge($filters, ['cursor' => $page->nextCursor()]));
    }
}
