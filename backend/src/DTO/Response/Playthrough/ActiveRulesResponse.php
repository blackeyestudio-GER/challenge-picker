<?php

namespace App\DTO\Response\Playthrough;

class ActiveRulesResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ActiveRulesResponseData $data
    ) {
    }

    /**
     * @param list<ActiveRuleResponseItem> $activeRules
     */
    public static function fromValues(int $playthroughId, string $status, array $activeRules): self
    {
        return new self(
            success: true,
            data: new ActiveRulesResponseData(
                playthroughId: $playthroughId,
                status: $status,
                activeRules: $activeRules
            )
        );
    }
}

class ActiveRulesResponseData
{
    /**
     * @param list<ActiveRuleResponseItem> $activeRules
     */
    public function __construct(
        public readonly int $playthroughId,
        public readonly string $status,
        public readonly array $activeRules
    ) {
    }
}

class ActiveRuleResponseItem
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $ruleId,
        public readonly ?string $ruleName,
        public readonly ?string $description,
        public readonly ?string $ruleType,
        public readonly string $type,
        public readonly ?int $currentAmount,
        public readonly ?int $initialAmount,
        public readonly ?int $durationSeconds,
        public readonly ?string $expiresAt,
        public readonly ?int $timeRemaining,
        public readonly ?string $startedAt
    ) {
    }
}
