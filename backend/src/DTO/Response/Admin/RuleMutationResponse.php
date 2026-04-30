<?php

namespace App\DTO\Response\Admin;

use App\DTO\Response\Rule\RuleResponse;

class RuleMutationResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly RuleMutationResponseData $data
    ) {
    }

    public static function fromValues(string $message, RuleResponse $rule): self
    {
        return new self(
            success: true,
            data: new RuleMutationResponseData($message, $rule)
        );
    }
}

class RuleMutationResponseData
{
    public function __construct(
        public readonly string $message,
        public readonly RuleResponse $rule
    ) {
    }
}
