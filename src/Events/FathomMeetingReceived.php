<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Events;

use Codepreneur\Fathom\Data\MeetingData;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class FathomMeetingReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public MeetingData $meeting,
        public Request $request,
    ) {}
}
