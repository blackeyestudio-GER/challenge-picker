<?php

namespace App\DTO\Response\Playthrough;

class CreatePlaythroughResponse
{
    public bool $success = true;
    public PlaythroughResponse $data;

    public static function fromPlaythrough(PlaythroughResponse $playthrough): self
    {
        $response = new self();
        $response->data = $playthrough;

        return $response;
    }
}
