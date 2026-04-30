<?php

namespace App\DTO\Response\Admin;

use App\DTO\Response\Ruleset\RulesetResponse;

class RulesetMutationResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly RulesetMutationResponseData $data
    ) {
    }

    public static function fromValues(string $message, RulesetResponse $ruleset): self
    {
        return new self(
            success: true,
            data: new RulesetMutationResponseData($message, $ruleset)
        );
    }
}

class RulesetMutationResponseData
{
    public function __construct(
        public readonly string $message,
        public readonly RulesetResponse $ruleset
    ) {
    }
}
