<?php

namespace App\DTO\Response\Game;

class GameCategoryResponse
{
    public int $id;
    public string $name;
    public string $slug;
    public int $voteCount;
    public bool $userVoted;
    public ?int $userVoteType; // 1 for upvote, -1 for downvote, null if not voted

    /**
     * @param array{id: int, name: string, slug: string, voteCount: int, userVoted?: bool, userVoteType?: int|null} $data
     */
    public static function fromArray(array $data): self
    {
        $response = new self();
        $response->id = $data['id'];
        $response->name = $data['name'];
        $response->slug = $data['slug'];
        $response->voteCount = $data['voteCount'];
        $response->userVoted = $data['userVoted'] ?? false;
        $response->userVoteType = $data['userVoteType'] ?? null;

        return $response;
    }
}
