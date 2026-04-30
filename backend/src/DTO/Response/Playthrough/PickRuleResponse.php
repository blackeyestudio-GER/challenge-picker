<?php

namespace App\DTO\Response\Playthrough;

class PickRuleResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly PickRuleResponseData $data
    ) {
    }

    public static function activated(int $ruleId, string $ruleName, string $message): self
    {
        return new self(
            success: true,
            data: new PickRuleResponseData(
                ruleId: $ruleId,
                ruleName: $ruleName,
                activated: true,
                position: null,
                eta: null,
                message: $message
            )
        );
    }

    public static function queued(int $ruleId, string $ruleName, int $position, int $eta, string $message): self
    {
        return new self(
            success: true,
            data: new PickRuleResponseData(
                ruleId: $ruleId,
                ruleName: $ruleName,
                activated: false,
                position: $position,
                eta: $eta,
                message: $message
            )
        );
    }
}
