<?php

namespace App\DTO\Response\Playthrough;

class CompletedPlaythroughsResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly CompletedPlaythroughsResponseData $data
    ) {
    }

    /**
     * @param list<PlaythroughResponse> $playthroughs
     */
    public static function fromPlaythroughs(array $playthroughs): self
    {
        return new self(
            success: true,
            data: new CompletedPlaythroughsResponseData($playthroughs)
        );
    }
}

class CompletedPlaythroughsResponseData
{
    /**
     * @param list<PlaythroughResponse> $playthroughs
     */
    public function __construct(
        public readonly array $playthroughs
    ) {
    }
}
