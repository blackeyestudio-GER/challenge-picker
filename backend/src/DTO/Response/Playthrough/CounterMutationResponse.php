<?php

namespace App\DTO\Response\Playthrough;

class CounterMutationResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly CounterMutationResponseData $data
    ) {
    }

    public static function fromValues(?int $id, ?int $currentAmount, bool $isActive, string $message): self
    {
        return new self(
            success: true,
            data: new CounterMutationResponseData(
                id: $id,
                currentAmount: $currentAmount,
                isActive: $isActive,
                message: $message
            )
        );
    }
}

class CounterMutationResponseData
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $currentAmount,
        public readonly bool $isActive,
        public readonly string $message
    ) {
    }
}
