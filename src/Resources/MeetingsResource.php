<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Resources;

use Codepreneur\Fathom\Data\MeetingData;
use Codepreneur\Fathom\Data\MeetingTypeData;
use Codepreneur\Fathom\Data\PaginatedResponse;
use Codepreneur\Fathom\FathomClient;

class MeetingsResource
{
    public function __construct(
        protected FathomClient $client,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return PaginatedResponse<MeetingData>
     */
    public function list(array $filters = []): PaginatedResponse
    {
        $data = $this->client->get('meetings', $filters);

        return PaginatedResponse::fromArray($data, MeetingData::fromArray(...));
    }

    /**
     * @param  PaginatedResponse<MeetingData>  $page
     * @param  array<string, mixed>  $filters
     * @return PaginatedResponse<MeetingData>
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

    /**
     * @param  array<string, mixed>  $filters
     * @return PaginatedResponse<MeetingTypeData>
     */
    public function types(array $filters = []): PaginatedResponse
    {
        $data = $this->client->get('meeting_types', $filters);

        return PaginatedResponse::fromArray($data, MeetingTypeData::fromArray(...));
    }

    /**
     * @param  PaginatedResponse<MeetingTypeData>  $page
     * @param  array<string, mixed>  $filters
     * @return PaginatedResponse<MeetingTypeData>
     */
    public function nextTypes(PaginatedResponse $page, array $filters = []): PaginatedResponse
    {
        if (! $page->hasMore()) {
            return new PaginatedResponse(
                limit: $page->limit,
                nextCursor: null,
                items: [],
            );
        }

        return $this->types(array_merge($filters, ['cursor' => $page->nextCursor()]));
    }
}
