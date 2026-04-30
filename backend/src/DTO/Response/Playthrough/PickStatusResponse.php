<?php

namespace App\DTO\Response\Playthrough;

class PickStatusResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly PickStatusResponseData $data
    ) {
    }

    /**
     * @param list<int> $cooldownRuleIds
     */
    public static function fromValues(
        bool $canPick,
        ?int $rateLimitSeconds,
        array $cooldownRuleIds,
        int $availableRulesCount,
        string $message
    ): self {
        return new self(
            success: true,
            data: new PickStatusResponseData(
                canPick: $canPick,
                rateLimitSeconds: $rateLimitSeconds,
                cooldownRuleIds: $cooldownRuleIds,
                availableRulesCount: $availableRulesCount,
                message: $message
            )
        );
    }
}

class PickStatusResponseData
{
    /**
     * @param list<int> $cooldownRuleIds
     */
    public function __construct(
        public readonly bool $canPick,
        public readonly ?int $rateLimitSeconds,
        public readonly array $cooldownRuleIds,
        public readonly int $availableRulesCount,
        public readonly string $message
    ) {
    }
}
