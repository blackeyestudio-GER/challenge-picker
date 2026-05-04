<?php

namespace App\DTO\Request\User;

use Symfony\Component\Validator\Constraints as Assert;

class DeleteAccountRequest
{
    #[Assert\NotBlank(message: 'Please type DELETE to confirm account deletion')]
    #[Assert\EqualTo(value: 'DELETE', message: 'Please type DELETE exactly to confirm account deletion')]
    public string $confirmText = '';

    public ?string $currentPassword = null;
}
