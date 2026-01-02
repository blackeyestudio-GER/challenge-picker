<?php

namespace App\Command;

use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fetch-game-icons',
    description: 'Fetch game icons from SteamGridDB and store them as base64',
)]
class FetchGameIconsCommand extends Command
{
    private ?string $twitchDefaultHash = null;
    private ?string $kickDefaultHash = null;

    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Fetching Game Icons from Steam');

        // Download and cache default placeholder images
        $io->section('Downloading default placeholder images...');
        $this->downloadDefaultPlaceholders($io);

        $games = $this->gameRepository->findAll();
        $io->progressStart(count($games));

        $updated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($games as $game) {
            try {
                $gameName = $game->getName();

                // Check if game has an existing image
                $existingImage = $game->getImage();
                if ($existingImage !== null) {
                    // Check if it's the default Twitch placeholder
                    if ($this->isDefaultTwitchPlaceholder($existingImage)) {
                        $io->writeln("Removing default placeholder for: {$gameName}");
                        $game->setImage(null);
                        $this->entityManager->flush();
                        // Continue processing to fetch a real image
                    } else {
                        // Has a real image, skip
                        ++$skipped;
                        $io->progressAdvance();
                        continue;
                    }
                }

                $io->writeln("Processing: {$gameName}");

                // Try sources in priority order: Twitch > Kick > Steam
                $base64Image = null;
                $source = null;

                // 1. Try Twitch first (most reliable for game box art)
                $twitchCategory = $game->getTwitchCategory();
                if ($twitchCategory) {
                    // Strip year from category name (Twitch categories don't include years)
                    $twitchCategoryClean = $this->stripYearFromName($twitchCategory);
                    $result = $this->tryFetchFromTwitch($twitchCategoryClean, $io);
                    if ($result !== null) {
                        $base64Image = $result;
                        $source = 'Twitch';
                        $io->writeln('  ✓ Found valid image on Twitch');
                    } else {
                        $io->writeln('  ✗ Twitch returned placeholder or no image');
                    }
                }

                // 2. Try Kick if Twitch didn't work (disabled for now - no getKickCategory method)
                // if (!$base64Image) {
                //     $kickCategory = $game->getKickCategory();
                //     if ($kickCategory) {
                //         $kickCategoryClean = $this->stripYearFromName($kickCategory);
                //         $result = $this->tryFetchFromKick($kickCategoryClean, $io);
                //         if ($result !== null) {
                //             $base64Image = $result;
                //             $source = 'Kick';
                //             $io->writeln('  ✓ Found valid image on Kick');
                //         } else {
                //             $io->writeln('  ✗ Kick returned placeholder or no image');
                //         }
                //     }
                // }

                // 3. Try Steam if Twitch and Kick didn't work
                if (!$base64Image) {
                    $steamLink = $game->getSteamLink();
                    if ($steamLink) {
                        $appId = $this->extractSteamAppId($steamLink);
                        if ($appId) {
                            $imageUrl = $this->getSteamImageUrl($appId);
                            if ($imageUrl) {
                                $base64Image = $this->downloadAndResizeImage($imageUrl, 256, 256);
                                if ($base64Image) {
                                    $source = 'Steam';
                                    $io->writeln('  ✓ Found on Steam');
                                }
                            }
                        }
                    }
                }

                // If no valid image found from any source
                if ($base64Image === null) {
                    $io->warning("No valid image found from any source for: {$gameName}");
                    ++$failed;
                    $io->progressAdvance();
                    continue;
                }

                // Update the game
                $game->setImage($base64Image);
                $this->entityManager->flush();

                ++$updated;
                $io->writeln("✓ Updated: {$gameName} (source: {$source})");

            } catch (\Exception $e) {
                $io->error("Error processing {$game->getName()}: " . $e->getMessage());
                ++$failed;
            }

            $io->progressAdvance();

            // Small delay to be respectful to Steam's CDN
            usleep(250000); // 0.25 seconds
        }

        $io->progressFinish();
        $io->success("Completed! Updated: {$updated}, Skipped: {$skipped}, Failed: {$failed}");

        return Command::SUCCESS;
    }

    private function extractSteamAppId(string $steamLink): ?string
    {
        // Extract Steam App ID from various URL formats:
        // https://store.steampowered.com/app/292030/The_Witcher_3_Wild_Hunt/
        // https://store.steampowered.com/app/292030

        if (preg_match('/\/app\/(\d+)/', $steamLink, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function getSteamImageUrl(string $appId): ?string
    {
        // Try multiple Steam CDN URLs for game images
        // We'll test each one and return the first that works

        $urls = [
            // Header capsule - most common and reliable
            "https://cdn.cloudflare.steamstatic.com/steam/apps/{$appId}/header.jpg",
            // Library art
            "https://cdn.cloudflare.steamstatic.com/steam/apps/{$appId}/library_600x900.jpg",
            // Capsule art
            "https://cdn.cloudflare.steamstatic.com/steam/apps/{$appId}/capsule_616x353.jpg",
            // Alternative CDN
            "https://cdn.akamai.steamstatic.com/steam/apps/{$appId}/header.jpg",
        ];

        foreach ($urls as $url) {
            // Check if URL is accessible using curl
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return $url;
            }
        }

        return null;
    }

    /**
     * @param positive-int $width
     * @param positive-int $height
     */
    private function downloadAndResizeImage(string $url, int $width, int $height): ?string
    {
        try {
            // Download the image using curl
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For HTTPS
            $imageData = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode !== 200) {
                \error_log("HTTP code {$httpCode} for URL: {$url}");

                return null;
            }

            if (!is_string($imageData) || $imageData === '') {
                \error_log("Empty or invalid image data for URL: {$url}. Curl error: {$error}");

                return null;
            }

            // Use the shared conversion method
            return $this->convertImageDataToBase64($imageData, $width, $height);

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Strip year from game/category name (e.g., "DOOM (2016)" -> "DOOM").
     * Twitch and Kick categories don't include years in their names.
     */
    private function stripYearFromName(string $name): string
    {
        // Remove patterns like " (2016)", " (1994)", etc.
        $result = preg_replace('/\s*\(\d{4}\)\s*$/', '', $name);

        return $result ?? $name;
    }

    /**
     * Check if an image is the default Twitch placeholder.
     * The default placeholder is typically very small (< 5KB) and generic.
     */
    private function isDefaultTwitchPlaceholder(string $base64Image): bool
    {
        // Extract the actual image data from base64
        if (preg_match('/^data:image\/\w+;base64,(.+)$/', $base64Image, $matches)) {
            $imageData = base64_decode($matches[1]);
            if (!$imageData) {
                return false;
            }

            // Check file size - default placeholder is very small (< 5KB)
            $fileSize = strlen($imageData);
            if ($fileSize < 5000) {
                return true;
            }

            // Could also check MD5 hash if we know the specific placeholder hashes
            // For now, size check is sufficient
        }

        return false;
    }

    /**
     * Download and cache default placeholder images from Twitch and Kick.
     */
    private function downloadDefaultPlaceholders(SymfonyStyle $io): void
    {
        // Download Twitch default placeholder
        // Twitch uses a generic placeholder image when no game art exists
        // We can get this by requesting a non-existent game category
        $twitchPlaceholderUrl = 'https://static-cdn.jtvnw.net/ttv-boxart/NONEXISTENT_GAME_PLACEHOLDER_12345-285x380.jpg';

        $ch = curl_init($twitchPlaceholderUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $twitchPlaceholderData = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && is_string($twitchPlaceholderData) && $twitchPlaceholderData !== '') {
            $this->twitchDefaultHash = md5($twitchPlaceholderData);
            $io->writeln("✓ Downloaded Twitch default placeholder (hash: {$this->twitchDefaultHash})");
        } else {
            $io->warning('Could not download Twitch default placeholder');
        }

        // Download Kick default placeholder
        // Kick also uses a generic placeholder for missing game art
        $kickPlaceholderUrl = 'https://files.kick.com/images/subcategories/116/tile/b81b7cc1-9db7-483d-a781-4bbb55be9d41';

        $ch = curl_init($kickPlaceholderUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $kickPlaceholderData = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && is_string($kickPlaceholderData) && $kickPlaceholderData !== '') {
            $this->kickDefaultHash = md5($kickPlaceholderData);
            $io->writeln("✓ Downloaded Kick default placeholder (hash: {$this->kickDefaultHash})");
        } else {
            $io->warning('Could not download Kick default placeholder');
        }
    }

    /**
     * Try to fetch a valid image from Twitch (not a placeholder).
     */
    private function tryFetchFromTwitch(string $twitchCategory, SymfonyStyle $io): ?string
    {
        $encodedCategory = urlencode($twitchCategory);

        // Try multiple sizes
        $sizes = [
            '600x800',  // High quality
            '285x380',  // Standard Twitch size
            '272x380',  // Alternative size
        ];

        foreach ($sizes as $size) {
            $url = "https://static-cdn.jtvnw.net/ttv-boxart/{$encodedCategory}-{$size}.jpg";

            // Download the image
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $imageData = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !is_string($imageData) || $imageData === '') {
                continue;
            }

            // Check if this is the default placeholder
            $imageHash = md5($imageData);

            // Check by hash (if we have the default hash)
            if ($this->twitchDefaultHash !== null && $imageHash === $this->twitchDefaultHash) {
                $io->writeln('  ✗ Twitch returned default placeholder (hash match)');
                continue;
            }

            // Also check by file size (< 5KB is likely a placeholder)
            $fileSize = strlen($imageData);
            if ($fileSize < 5000) {
                $io->writeln("  ✗ Twitch returned small image ({$fileSize} bytes), likely placeholder");
                continue;
            }

            // Valid image found! Process it
            $base64Image = $this->convertImageDataToBase64($imageData, 256, 256);
            if ($base64Image) {
                return $base64Image;
            }
        }

        return null;
    }

    /**
     * Try to fetch a valid image from Kick (not a placeholder).
     * Currently not implemented - returns null.
     *
     * @phpstan-ignore-next-line
     */
    private function tryFetchFromKick(string $kickCategory, SymfonyStyle $io): null
    {
        // Kick uses a different URL structure
        // Format: https://files.kick.com/images/subcategories/{id}/tile/{uuid}
        // For now, we'll try a generic approach using their API or known patterns

        // Note: Kick's API structure may require API calls to get the image URL
        // For this implementation, we'll skip detailed Kick support
        // but the structure is here for future implementation

        $io->writeln('  ⚠ Kick fetching not fully implemented yet');

        return null;
    }

    /**
     * Convert raw image data to base64 with resizing.
     *
     * @param positive-int $width
     * @param positive-int $height
     */
    private function convertImageDataToBase64(string $imageData, int $width, int $height): ?string
    {
        try {
            // Create image from data
            $image = \imagecreatefromstring($imageData);
            if ($image === false) {
                return null;
            }

            // Get original dimensions
            $origWidth = \imagesx($image);
            $origHeight = \imagesy($image);

            // Calculate crop to make it square (center crop)
            $size = min($origWidth, $origHeight);

            $offsetX = ($origWidth - $size) / 2;
            $offsetY = ($origHeight - $size) / 2;

            // Create new square image
            $squareImage = \imagecreatetruecolor($size, $size);
            if ($squareImage === false) {
                \imagedestroy($image);

                return null;
            }
            \imagecopyresampled($squareImage, $image, 0, 0, (int) $offsetX, (int) $offsetY, $size, $size, $size, $size);

            // Resize to target size
            $resizedImage = \imagecreatetruecolor($width, $height);
            if ($resizedImage === false) {
                \imagedestroy($image);
                \imagedestroy($squareImage);

                return null;
            }
            \imagecopyresampled($resizedImage, $squareImage, 0, 0, 0, 0, $width, $height, $size, $size);

            // Convert to base64
            \ob_start();
            \imagejpeg($resizedImage, null, 85);
            $finalImageData = \ob_get_clean();

            // Clean up
            \imagedestroy($image);
            \imagedestroy($squareImage);
            \imagedestroy($resizedImage);

            // Check if output buffering succeeded
            if (!$finalImageData) {
                return null;
            }

            return 'data:image/jpeg;base64,' . base64_encode($finalImageData);

        } catch (\Exception $e) {
            return null;
        }
    }
}
