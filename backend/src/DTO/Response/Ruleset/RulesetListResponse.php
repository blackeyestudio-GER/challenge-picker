<?php

namespace App\DTO\Response\Ruleset;

class RulesetListResponse
{
    public bool $success = true;

    /** @var RulesetResponse[] */
    public array $data = [];

    public int $totalCount = 0;
    public int $gameSpecificCount = 0;
    public int $categoryBasedCount = 0;

    /**
     * @param RulesetResponse[] $rulesets
     */
    public static function fromRulesets(array $rulesets): self
    {
        $response = new self();
        $response->data = $rulesets;
        $response->totalCount = count($rulesets);
        $response->gameSpecificCount = count(array_filter($rulesets, fn ($r) => $r->isGameSpecific));
        $response->categoryBasedCount = count(array_filter($rulesets, fn ($r) => !$r->isGameSpecific));

        return $response;
    }
}
