<?php

namespace App\DTO\Response\Admin;

class PayoutDecisionResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly PayoutDecisionResponseData $data
    ) {
    }

    public static function fromValues(PayoutDecisionItem $payoutRequest): self
    {
        return new self(
            success: true,
            data: new PayoutDecisionResponseData($payoutRequest)
        );
    }
}

class PayoutDecisionResponseData
{
    public function __construct(
        public readonly PayoutDecisionItem $payoutRequest
    ) {
    }
}

class PayoutDecisionItem
{
    public function __construct(
        public readonly int $id,
        public readonly string $status,
        public readonly ?string $processedAt
    ) {
    }
}
