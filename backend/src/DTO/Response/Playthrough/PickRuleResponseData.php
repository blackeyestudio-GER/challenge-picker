<?php

namespace App\DTO\Response\Playthrough;

class PickRuleResponseData
{
    public function __construct(
        public readonly int $ruleId,
        public readonly string $ruleName,
        public readonly bool $activated,
        public readonly ?int $position,
        public readonly ?int $eta,
        public readonly string $message
    ) {
    }
}
