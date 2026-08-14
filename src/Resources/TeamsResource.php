<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Resources;

use Codepreneur\Fathom\Data\PaginatedResponse;
use Codepreneur\Fathom\Data\TeamData;
use Codepreneur\Fathom\FathomClient;

class TeamsResource
{
    public function __construct(
        protected FathomClient $client,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return PaginatedResponse<TeamData>
     */
    public function list(array $filters = []): PaginatedResponse
    {
        $data = $this->client->get('teams', $filters);

        return PaginatedResponse::fromArray($data, TeamData::fromArray(...));
    }

    /**
     * @param  PaginatedResponse<TeamData>  $page
     * @param  array<string, mixed>  $filters
     * @return PaginatedResponse<TeamData>
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
