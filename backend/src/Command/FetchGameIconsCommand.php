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

                // Try sources in priority order: Twitch > Steam > Epic
                $imageUrl = null;
                $source = null;

                // 1. Try Twitch first (most reliable for game box art)
                $twitchCategory = $game->getTwitchCategory();
                if ($twitchCategory) {
                    // Strip year from category name (Twitch categories don't include years)
                    $twitchCategoryClean = $this->stripYearFromName($twitchCategory);
                    $imageUrl = $this->getTwitchImageUrl($twitchCategoryClean);
                    if ($imageUrl) {
                        $source = 'Twitch';
                        $io->writeln('  ✓ Found on Twitch');
                    }
                }

                // 2. Try Steam if Twitch didn't work
                if (!$imageUrl) {
                    $steamLink = $game->getSteamLink();
                    if ($steamLink) {
                        $appId = $this->extractSteamAppId($steamLink);
                        if ($appId) {
                            $imageUrl = $this->getSteamImageUrl($appId);
                            if ($imageUrl) {
                                $source = 'Steam';
                                $io->writeln('  ✓ Found on Steam');
                            }
                        }
                    }
                }

                // 3. Try Epic if Steam didn't work
                if (!$imageUrl) {
                    $epicLink = $game->getEpicLink();
                    if ($epicLink) {
                        // Epic uses a different URL structure, we'd need to parse it
                        // For now, skip Epic as it's more complex
                        $io->note('  Epic link exists but parser not implemented yet');
                    }
                }

                // If no image URL found from any source
                if ($imageUrl === null) {
                    $io->warning("No image found from any source for: {$gameName}");
                    ++$failed;
                    $io->progressAdvance();
                    continue;
                }

                $io->writeln("  Image URL ({$source}): {$imageUrl}");

                // Download and process the image
                $base64Image = $this->downloadAndResizeImage($imageUrl, 256, 256);

                if ($base64Image === null) {
                    $io->warning("Failed to download/process image for: {$gameName} from {$imageUrl}");
                    ++$failed;
                    $io->progressAdvance();
                    continue;
                }

                // Update the game
                $game->setImage($base64Image);
                $this->entityManager->flush();

                ++$updated;
                $io->writeln("✓ Updated: {$gameName}");

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

    private function getTwitchImageUrl(string $twitchCategory): ?string
    {
        // Twitch box art URL format:
        // https://static-cdn.jtvnw.net/ttv-boxart/{game_name}-{width}x{height}.jpg
        // URL encode the category name
        $encodedCategory = urlencode($twitchCategory);

        // Try multiple sizes (higher quality first, then fall back)
        $sizes = [
            '600x800',  // High quality
            '285x380',  // Standard Twitch size
            '272x380',  // Alternative size
        ];

        foreach ($sizes as $size) {
            $url = "https://static-cdn.jtvnw.net/ttv-boxart/{$encodedCategory}-{$size}.jpg";

            // Check if URL is accessible and download to verify it's not the default placeholder
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $imageData) {
                // Check if this is the default Twitch placeholder by checking image size
                // The default placeholder is a small generic image
                // We can detect it by checking if the file size is suspiciously small (< 5KB)
                $fileSize = strlen($imageData);

                // Also check for the known default placeholder hash
                $imageHash = md5($imageData);
                $knownDefaultHashes = [
                    // Add known Twitch default placeholder hashes here if we find them
                    // For now, we'll rely on file size check
                ];

                if ($fileSize < 5000 || in_array($imageHash, $knownDefaultHashes)) {
                    // This is likely the default placeholder, skip it
                    continue;
                }

                return $url;
            }
        }

        return null;
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
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode !== 200) {
                \error_log("HTTP code {$httpCode} for URL: {$url}");

                return null;
            }

            if ($imageData === false || empty($imageData)) {
                \error_log("Empty or false image data for URL: {$url}. Curl error: {$error}");

                return null;
            }

            // Create image from downloaded data
            $image = \imagecreatefromstring($imageData);
            if ($image === false) {
                \error_log('Failed to create image from data. Data length: ' . \strlen($imageData));

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
            \imagecopyresampled($squareImage, $image, 0, 0, (int) $offsetX, (int) $offsetY, $size, $size, $size, $size);

            // Resize to target size
            $resizedImage = \imagecreatetruecolor($width, $height);
            \imagecopyresampled($resizedImage, $squareImage, 0, 0, 0, 0, $width, $height, $size, $size);

            // Convert to base64
            \ob_start();
            \imagejpeg($resizedImage, null, 85);
            $finalImageData = \ob_get_clean();

            // Clean up
            \imagedestroy($image);
            \imagedestroy($squareImage);
            \imagedestroy($resizedImage);

            if (empty($finalImageData)) {
                return null;
            }

            return 'data:image/jpeg;base64,' . base64_encode($finalImageData);

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
        return preg_replace('/\s*\(\d{4}\)\s*$/', '', $name);
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
            if ($imageData === false) {
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
}
