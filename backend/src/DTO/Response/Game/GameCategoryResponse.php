<?php

namespace App\DTO\Response\Game;

class GameCategoryResponse
{
    public int $id;
    public string $name;
    public string $slug;
    public int $voteCount;
    public bool $userVoted;

    public static function fromArray(array $data): self
    {
        $response = new self();
        $response->id = $data['id'];
        $response->name = $data['name'];
        $response->slug = $data['slug'];
        $response->voteCount = (int)$data['voteCount'];
        $response->userVoted = $data['userVoted'] ?? false;

        return $response;
    }
}

