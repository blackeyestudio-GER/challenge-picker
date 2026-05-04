<?php

namespace App\Controller\Api\User;

use App\DTO\Response\User\UserResponse;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ArrayTypeHelper;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordCallbackController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly JWTTokenManagerInterface $jwtManager
    ) {
    }

    #[Route('/api/user/connect/discord/callback', name: 'api_user_connect_discord_callback', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $code = $request->query->get('code');
        $state = $request->query->get('state');

        // Decode state to get user UUID (if connecting to existing account)
        $decodedState = is_string($state) ? base64_decode($state, true) : false;
        if ($decodedState === false) {
            $stateData = [];
        } else {
            $stateData = json_decode($decodedState, true);
            if (!is_array($stateData)) {
                $stateData = [];
            }
        }
        /** @var array<string, mixed> $stateData */
        $userUuid = ArrayTypeHelper::tryGetString($stateData, 'user_uuid');

        $user = null;
        if ($userUuid) {
            $user = $this->userRepository->findOneBy(['uuid' => $userUuid]);
        }

        if (!is_string($code) || $code === '') {
            return new Response('<html><body><script>window.close();</script><p>Authorization cancelled. You can close this window.</p></body></html>');
        }

        $clientId = isset($_ENV['DISCORD_CLIENT_ID']) && is_string($_ENV['DISCORD_CLIENT_ID'])
            ? $_ENV['DISCORD_CLIENT_ID']
            : throw new \RuntimeException('DISCORD_CLIENT_ID not configured');
        $clientSecret = isset($_ENV['DISCORD_CLIENT_SECRET']) && is_string($_ENV['DISCORD_CLIENT_SECRET'])
            ? $_ENV['DISCORD_CLIENT_SECRET']
            : throw new \RuntimeException('DISCORD_CLIENT_SECRET not configured');
        $redirectUri = isset($_ENV['DISCORD_REDIRECT_URI']) && is_string($_ENV['DISCORD_REDIRECT_URI'])
            ? $_ENV['DISCORD_REDIRECT_URI']
            : 'http://localhost:8090/api/user/connect/discord/callback';
        $frontendUrl = isset($_ENV['FRONTEND_URL']) && is_string($_ENV['FRONTEND_URL'])
            ? $_ENV['FRONTEND_URL']
            : 'http://localhost:3000';

        try {
            // Exchange code for access token
            $tokenResponse = $this->httpClient->request('POST', 'https://discord.com/api/oauth2/token', [
                'body' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            /** @var array<string, mixed> $tokenData */
            $tokenData = $tokenResponse->toArray();
            $accessToken = ArrayTypeHelper::getString($tokenData, 'access_token');

            // Get Discord user info
            $userResponse = $this->httpClient->request('GET', 'https://discord.com/api/users/@me', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            /** @var array<string, mixed> $discordUser */
            $discordUser = $userResponse->toArray();

            // Check if Discord account is already connected to another user
            $discordId = ArrayTypeHelper::getString($discordUser, 'id');
            $username = ArrayTypeHelper::getString($discordUser, 'username');
            $discriminator = ArrayTypeHelper::tryGetString($discordUser, 'discriminator') ?? '0';
            $avatar = ArrayTypeHelper::tryGetString($discordUser, 'avatar');
            $email = ArrayTypeHelper::tryGetString($discordUser, 'email') ?? ($discordId . '@discord.local');
            $discordDisplayName = $username . '#' . $discriminator;
            $discordAvatarUrl = $avatar !== null
                ? sprintf('https://cdn.discordapp.com/avatars/%s/%s.png', $discordId, $avatar)
                : null;
            $existingUser = $this->userRepository->findOneBy(['discordId' => $discordId]);

            if ($existingUser && $user && $existingUser->getUuid() !== $user->getUuid()) {
                return new Response(
                    '<html><body><script>window.opener.postMessage({type:"discord_error",message:"This Discord account is already connected to another user"}, "*");window.close();</script></body></html>'
                );
            }

            // If user is logged in, connect Discord to their account
            if ($user) {
                $user->setDiscordId($discordId);
                $user->setDiscordUsername($discordDisplayName);
                $user->setDiscordAvatar($discordAvatarUrl);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                // Return success HTML that closes window and notifies parent
                return new Response(
                    '<html><body><script>window.opener.postMessage({type:"discord_connected",username:"' . $user->getDiscordUsername() . '"}, "*");window.close();</script><p>Discord connected successfully! You can close this window.</p></body></html>'
                );
            }

            // If user is not logged in and state action is 'login', handle login/registration
            if (isset($stateData['action']) && $stateData['action'] === 'login') {
                // Check if Discord account exists
                if ($existingUser) {
                    // User exists, generate JWT token and login
                    $token = $this->jwtManager->create($existingUser);
                    $userResponse = UserResponse::fromEntity($existingUser);
                    $userResponseJson = json_encode($userResponse, JSON_THROW_ON_ERROR);

                    return new Response(
                        '<html><body><script>
                        try {
                            if (window.opener && !window.opener.closed) {
                                window.opener.postMessage({type:"discord_login_success",token:"' . $token . '",user:' . $userResponseJson . '}, "' . $frontendUrl . '");
                                setTimeout(function() { window.close(); }, 500);
                            } else {
                                // Fallback: redirect to frontend with token in URL (will be handled there)
                                window.location.href = "' . $frontendUrl . '/login?discord_token=' . urlencode($token) . '&discord_success=1";
                            }
                        } catch(e) {
                            document.body.innerHTML = "<p>Login successful! Token: ' . substr($token, 0, 20) . '...</p><p>Please close this window and refresh the login page.</p>";
                        }
                    </script><p>Logged in successfully! Redirecting...</p></body></html>'
                    );
                }

                // Discord account doesn't exist, create new user and register
                $newUser = new User();
                $newUser->setEmail($email);
                $newUser->setUsername($discordDisplayName);
                $newUser->setDiscordId($discordId);
                $newUser->setDiscordUsername($discordDisplayName);
                $newUser->setDiscordAvatar($discordAvatarUrl);
                $newUser->setOauthProvider('discord');
                $newUser->setOauthId($discordId);
                $newUser->setAvatar($discordAvatarUrl);

                $this->entityManager->persist($newUser);
                $this->entityManager->flush();

                // Generate JWT token for new user
                $token = $this->jwtManager->create($newUser);
                $userResponse = UserResponse::fromEntity($newUser);
                $userResponseJson = json_encode($userResponse, JSON_THROW_ON_ERROR);

                return new Response(
                    '<html><body><script>
                        try {
                            if (window.opener && !window.opener.closed) {
                                window.opener.postMessage({type:"discord_login_success",token:"' . $token . '",user:' . $userResponseJson . '}, "' . $frontendUrl . '");
                                setTimeout(function() { window.close(); }, 1000);
                            } else {
                                // Fallback: redirect to frontend with token in URL
                                window.location.href = "' . $frontendUrl . '/login?discord_token=' . urlencode($token) . '&discord_success=1";
                            }
                        } catch(e) {
                            document.body.innerHTML = "<p>Account created! Token: ' . substr($token, 0, 20) . '...</p><p>Please close this window and refresh the login page.</p>";
                        }
                    </script><p>Account created! Redirecting...</p></body></html>'
                );
            }

            // Invalid state
            return new Response(
                '<html><body><script>window.opener.postMessage({type:"discord_error",message:"Invalid OAuth state"}, "*");window.close();</script></body></html>'
            );

        } catch (\Exception $e) {
            return new Response(
                '<html><body><script>
                    try {
                        if (window.opener) {
                            window.opener.postMessage({type:"discord_error",message:"Failed to connect Discord: ' . addslashes($e->getMessage()) . '"}, "*");
                            setTimeout(function() { window.close(); }, 1000);
                        } else {
                            document.body.innerHTML = "<h2>Login Error</h2><p>' . addslashes($e->getMessage()) . '</p><p><a href=\"http://localhost:3000/login\">Back to Login</a></p>";
                        }
                    } catch(err) {
                        document.body.innerHTML = "<h2>Login Error</h2><p>Please close this window and try again.</p>";
                    }
                </script></body></html>'
            );
        }
    }
}
