<?php

namespace App\DTO\Response\Admin;

class PayoutRequestsResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly PayoutRequestsResponseData $data
    ) {
    }

    /**
     * @param list<PayoutRequestItem> $payoutRequests
     */
    public static function fromItems(array $payoutRequests): self
    {
        return new self(
            success: true,
            data: new PayoutRequestsResponseData($payoutRequests)
        );
    }
}

class PayoutRequestsResponseData
{
    /**
     * @param list<PayoutRequestItem> $payoutRequests
     */
    public function __construct(
        public readonly array $payoutRequests
    ) {
    }
}

class PayoutRequestItem
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $designerUuid,
        public readonly ?string $designerUsername,
        public readonly ?string $designerEmail,
        public readonly string $amount,
        public readonly ?string $currency,
        public readonly ?string $status,
        public readonly bool $isAutomated,
        public readonly ?string $requestedAt
    ) {
    }
}
