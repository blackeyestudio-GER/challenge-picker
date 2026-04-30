<?php

namespace App\DTO\Response\Play;

class DashboardResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly DashboardResponseData $data
    ) {
    }

    /**
     * @param list<array{id: int, ruleId: int|null, ruleName: string|null, ruleType: string|null, type: string, currentAmount: int|null, initialAmount: int|null, durationSeconds: int|null, expiresAt: string|null, timeRemaining: int|null, startedAt: string|null}> $activeRules
     * @param array{canPick: bool, rateLimitSeconds: int|null, cooldownRuleIds: list<int>, availableRulesCount: int, message: string}|null $pickStatus
     * @param array{queueLength: int, pendingRules: list<array{ruleId: int, ruleName: string, ruleType: string|null, position: int, eta: int}>} $queueStatus
     */
    public static function fromValues(
        PlayScreenData $playthrough,
        array $activeRules,
        ?array $pickStatus,
        array $queueStatus,
        bool $isHost
    ): self {
        return new self(
            success: true,
            data: new DashboardResponseData(
                playthrough: $playthrough,
                activeRules: array_map(
                    static fn (array $rule): DashboardActiveRuleData => DashboardActiveRuleData::fromArray($rule),
                    $activeRules
                ),
                pickStatus: $pickStatus !== null ? DashboardPickStatusData::fromArray($pickStatus) : null,
                queueStatus: DashboardQueueStatusData::fromArray($queueStatus),
                isHost: $isHost
            )
        );
    }
}

class DashboardResponseData
{
    /**
     * @param list<DashboardActiveRuleData> $activeRules
     */
    public function __construct(
        public readonly PlayScreenData $playthrough,
        public readonly array $activeRules,
        public readonly ?DashboardPickStatusData $pickStatus,
        public readonly DashboardQueueStatusData $queueStatus,
        public readonly bool $isHost
    ) {
    }
}

class DashboardActiveRuleData
{
    /**
     * @param array{id: int, ruleId: int|null, ruleName: string|null, ruleType: string|null, type: string, currentAmount: int|null, initialAmount: int|null, durationSeconds: int|null, expiresAt: string|null, timeRemaining: int|null, startedAt: string|null} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            ruleId: $data['ruleId'],
            ruleName: $data['ruleName'],
            ruleType: $data['ruleType'],
            type: $data['type'],
            currentAmount: $data['currentAmount'],
            initialAmount: $data['initialAmount'],
            durationSeconds: $data['durationSeconds'],
            expiresAt: $data['expiresAt'],
            timeRemaining: $data['timeRemaining'],
            startedAt: $data['startedAt']
        );
    }

    public function __construct(
        public readonly int $id,
        public readonly ?int $ruleId,
        public readonly ?string $ruleName,
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

class DashboardPickStatusData
{
    /**
     * @param array{canPick: bool, rateLimitSeconds: int|null, cooldownRuleIds: list<int>, availableRulesCount: int, message: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            canPick: $data['canPick'],
            rateLimitSeconds: $data['rateLimitSeconds'],
            cooldownRuleIds: $data['cooldownRuleIds'],
            availableRulesCount: $data['availableRulesCount'],
            message: $data['message']
        );
    }

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

class DashboardQueueStatusData
{
    /**
     * @param array{queueLength: int, pendingRules: list<array{ruleId: int, ruleName: string, ruleType: string|null, position: int, eta: int}>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            queueLength: $data['queueLength'],
            pendingRules: array_map(
                static fn (array $rule): DashboardQueuePendingRuleData => DashboardQueuePendingRuleData::fromArray($rule),
                $data['pendingRules']
            )
        );
    }

    /**
     * @param list<DashboardQueuePendingRuleData> $pendingRules
     */
    public function __construct(
        public readonly int $queueLength,
        public readonly array $pendingRules
    ) {
    }
}

class DashboardQueuePendingRuleData
{
    /**
     * @param array{ruleId: int, ruleName: string, ruleType: string|null, position: int, eta: int} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ruleId: $data['ruleId'],
            ruleName: $data['ruleName'],
            ruleType: $data['ruleType'],
            position: $data['position'],
            eta: $data['eta']
        );
    }

    public function __construct(
        public readonly int $ruleId,
        public readonly string $ruleName,
        public readonly ?string $ruleType,
        public readonly int $position,
        public readonly int $eta
    ) {
    }
}
