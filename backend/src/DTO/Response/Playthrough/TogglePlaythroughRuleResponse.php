<?php

namespace App\DTO\Response\Playthrough;

class TogglePlaythroughRuleResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly TogglePlaythroughRuleResponseData $data
    ) {
    }

    public static function fromValues(?int $id, ?int $ruleId, bool $isActive): self
    {
        return new self(
            success: true,
            data: new TogglePlaythroughRuleResponseData(
                id: $id,
                ruleId: $ruleId,
                isActive: $isActive
            )
        );
    }
}

class TogglePlaythroughRuleResponseData
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $ruleId,
        public readonly bool $isActive
    ) {
    }
}
