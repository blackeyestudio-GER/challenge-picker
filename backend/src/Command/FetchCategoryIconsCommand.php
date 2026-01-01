<?php

namespace App\Command;

use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fetch-category-icons',
    description: 'Fetch category icons from Kick.com and store them as base64',
)]
class FetchCategoryIconsCommand extends Command
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Fetching Category Icons from Kick.com');

        $categories = $this->categoryRepository->findAll();
        $io->progressStart(count($categories));

        $updated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($categories as $category) {
            try {
                // Skip if category already has an image
                if ($category->getImage() !== null) {
                    ++$skipped;
                    $io->progressAdvance();
                    continue;
                }

                $categoryName = $category->getName();
                $kickCategory = $category->getKickCategory();

                if (!$kickCategory) {
                    $io->writeln("  ⚠ No Kick category set for: {$categoryName}");
                    ++$failed;
                    $io->progressAdvance();
                    continue;
                }

                // Strip year from Kick category name (Kick categories don't include years)
                $kickCategoryClean = $this->stripYearFromName($kickCategory);

                $io->writeln("Processing: {$categoryName} (Kick: {$kickCategoryClean})");

                $imageUrl = $this->getKickImageUrl($kickCategoryClean);

                if ($imageUrl === null) {
                    $io->warning("No image found for: {$categoryName}");
                    ++$failed;
                    $io->progressAdvance();
                    continue;
                }

                $io->writeln("  Image URL (Kick): {$imageUrl}");

                // Download and process the image
                $base64Image = $this->downloadAndResizeImage($imageUrl, 256, 256);

                if ($base64Image === null) {
                    $io->warning("Failed to download/process image for: {$categoryName} from {$imageUrl}");
                    ++$failed;
                    $io->progressAdvance();
                    continue;
                }

                // Update the category
                $category->setImage($base64Image);
                $this->entityManager->flush();

                ++$updated;
                $io->writeln("✓ Updated: {$categoryName}");

            } catch (\Exception $e) {
                $io->error("Error processing {$category->getName()}: " . $e->getMessage());
                ++$failed;
            }

            $io->progressAdvance();

            // Small delay to be respectful
            usleep(250000); // 0.25 seconds
        }

        $io->progressFinish();
        $io->success("Completed! Updated: {$updated}, Skipped: {$skipped}, Failed: {$failed}");

        return Command::SUCCESS;
    }

    private function getKickImageUrl(string $kickCategory): ?string
    {
        // Kick.com category image URL format
        // Example: https://files.kick.com/images/subcategories/271/cover/c0d1ea43-4401-469c-ba10-29d301d4dc11
        // We'll use their CDN pattern - they use subcategory covers

        // Try to fetch the category page to get the actual image URL
        $categorySlug = strtolower(str_replace(' ', '-', $kickCategory));
        $pageUrl = "https://kick.com/categories/{$categorySlug}";

        // Fetch the page content
        $ch = curl_init($pageUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$html) {
            return null;
        }

        // Try to extract image URL from the HTML
        // Look for og:image or category cover images
        if (preg_match('/<meta property="og:image" content="([^"]+)"/', $html, $matches)) {
            return $matches[1];
        }

        // Try to find image URLs in the page
        if (preg_match('/https:\/\/files\.kick\.com\/images\/[^"\']+/', $html, $matches)) {
            return $matches[0];
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
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
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
     * Strip year from category name (e.g., "Retro Shooter (2020)" -> "Retro Shooter").
     * Kick categories don't include years in their names.
     */
    private function stripYearFromName(string $name): string
    {
        // Remove patterns like " (2016)", " (1994)", etc.
        return preg_replace('/\s*\(\d{4}\)\s*$/', '', $name);
    }
}
