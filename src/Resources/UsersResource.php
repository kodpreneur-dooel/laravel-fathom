<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Resources;

use Codepreneur\Fathom\Data\PaginatedResponse;
use Codepreneur\Fathom\Data\UserData;
use Codepreneur\Fathom\FathomClient;

class UsersResource
{
    public function __construct(
        protected FathomClient $client,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return PaginatedResponse<UserData>
     */
    public function list(array $filters = []): PaginatedResponse
    {
        $data = $this->client->get('users', $filters);

        return PaginatedResponse::fromArray($data, UserData::fromArray(...));
    }

    /**
     * @param  PaginatedResponse<UserData>  $page
     * @param  array<string, mixed>  $filters
     * @return PaginatedResponse<UserData>
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
