<?php

namespace App\DTO\Response\Playthrough;

class BrowsePlaythroughResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly BrowsePlaythroughResponseData $data
    ) {
    }

    /**
     * @param list<BrowsePlaythroughItem> $playthroughs
     */
    public static function fromItems(array $playthroughs): self
    {
        return new self(
            success: true,
            data: new BrowsePlaythroughResponseData($playthroughs)
        );
    }
}

class BrowsePlaythroughResponseData
{
    /**
     * @param list<BrowsePlaythroughItem> $playthroughs
     */
    public function __construct(
        public readonly array $playthroughs
    ) {
    }
}
