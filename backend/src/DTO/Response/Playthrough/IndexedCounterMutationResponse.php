<?php

namespace App\DTO\Response\Playthrough;

class IndexedCounterMutationResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly IndexedCounterMutationResponseData $data
    ) {
    }

    public static function fromValues(
        int $ruleId,
        string $ruleName,
        int $previousAmount,
        int $currentAmount,
        bool $completed
    ): self {
        return new self(
            success: true,
            data: new IndexedCounterMutationResponseData(
                ruleId: $ruleId,
                ruleName: $ruleName,
                previousAmount: $previousAmount,
                currentAmount: $currentAmount,
                completed: $completed
            )
        );
    }
}

class IndexedCounterMutationResponseData
{
    public function __construct(
        public readonly int $ruleId,
        public readonly string $ruleName,
        public readonly int $previousAmount,
        public readonly int $currentAmount,
        public readonly bool $completed
    ) {
    }
}
