<?php

namespace App\Service;

/**
 * Twitch OAuth requires a registered app at https://dev.twitch.tv/console/apps.
 * Placeholder or empty env values disable linking in the API/UI.
 */
final class TwitchOAuthSettings
{
    private const PLACEHOLDER_CLIENT_ID = 'your_twitch_client_id_here';

    private const PLACEHOLDER_CLIENT_SECRET = 'your_twitch_client_secret_here';

    public function __construct(
        private readonly bool $featureEnabled
    ) {
    }

    public function isLinkingConfigured(): bool
    {
        if (!$this->featureEnabled) {
            return false;
        }

        $idRaw = $_ENV['TWITCH_CLIENT_ID'] ?? '';
        $secretRaw = $_ENV['TWITCH_CLIENT_SECRET'] ?? '';
        $id = is_string($idRaw) ? trim($idRaw) : '';
        $secret = is_string($secretRaw) ? trim($secretRaw) : '';

        if ('' === $id || '' === $secret) {
            return false;
        }

        if (self::PLACEHOLDER_CLIENT_ID === $id || self::PLACEHOLDER_CLIENT_SECRET === $secret) {
            return false;
        }

        return true;
    }
}
