<?php

namespace App\DTO\Response\Playthrough;

class BrowseAvailabilityResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly BrowseAvailabilityResponseData $data
    ) {
    }

    public static function fromValues(bool $available, int $count): self
    {
        return new self(
            success: true,
            data: new BrowseAvailabilityResponseData(
                available: $available,
                count: $count
            )
        );
    }
}

class BrowseAvailabilityResponseData
{
    public function __construct(
        public readonly bool $available,
        public readonly int $count
    ) {
    }
}
