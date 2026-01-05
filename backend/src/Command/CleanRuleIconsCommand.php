<?php

namespace App\Command;

use App\Repository\RuleIconRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clean-rule-icons',
    description: 'Clean and fix existing rule icons in the database (removes background rectangles, fixes colors)'
)]
class CleanRuleIconsCommand extends Command
{
    public function __construct(
        private readonly RuleIconRepository $ruleIconRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Cleaning Existing Rule Icons');
        $io->text('This will clean all existing icons in the database by:');
        $io->listing([
            'Removing background rectangles',
            'Replacing hardcoded colors with currentColor',
            'Fixing viewBox attributes',
            'Removing width/height attributes',
        ]);

        $icons = $this->ruleIconRepository->findAll();
        $total = count($icons);

        if ($total === 0) {
            $io->warning('No icons found in database.');

            return Command::SUCCESS;
        }

        $io->text(sprintf('Found %d icons to clean.', $total));
        
        if (!$io->confirm('Continue?', true)) {
            $io->info('Cancelled.');

            return Command::SUCCESS;
        }

        $io->progressStart($total);
        $cleaned = 0;
        $failed = [];

        foreach ($icons as $icon) {
            try {
                $originalSvg = $icon->getSvgContent();
                $cleanedSvg = $this->cleanSvgContent($originalSvg);

                // Only update if SVG was actually changed
                if ($cleanedSvg !== $originalSvg) {
                    $icon->setSvgContent($cleanedSvg);
                    $icon->setUpdatedAt(new \DateTimeImmutable());
                    ++$cleaned;
                }

                // Batch flush every 50 icons
                if ($cleaned % 50 === 0 && $cleaned > 0) {
                    $this->entityManager->flush();
                }
            } catch (\Exception $e) {
                $failed[] = sprintf('%s: %s', $icon->getIdentifier(), $e->getMessage());
            }

            $io->progressAdvance();
        }

        // Final flush
        $this->entityManager->flush();
        $io->progressFinish();

        $io->newLine();
        $io->success([
            sprintf('Cleaned: %d icons', $cleaned),
            sprintf('Unchanged: %d icons', $total - $cleaned - count($failed)),
            sprintf('Failed: %d icons', count($failed)),
        ]);

        if (count($failed) > 0) {
            $io->warning('Failed to clean:');
            $io->listing($failed);
        }

        return Command::SUCCESS;
    }

    private function cleanSvgContent(string $svgContent): string
    {
        // Remove XML declaration if present
        $cleaned = (string) preg_replace('/<\?xml[^?]*\?>/', '', $svgContent);

        // Remove background rectangles (common in game-icons.net SVGs)
        // Pattern: <path d="M0 0h512v512H0z"></path> - full viewport background
        $cleaned = (string) preg_replace('/<path\s+[^>]*d=["\'][Mm]\s*0\s*[, ]?\s*0\s*[hH]\s*[0-9]+\s*[vV]\s*[0-9]+\s*[Hh]\s*0\s*[zZ]["\'][^>]*\/?>/i', '', $cleaned);
        
        // Also remove rect elements that cover the full viewport
        $cleaned = (string) preg_replace('/<rect[^>]*x=["\']0["\'][^>]*y=["\']0["\'][^>]*width=["\'][0-9]+["\'][^>]*height=["\'][0-9]+["\'][^>]*\/?>/i', '', $cleaned);
        
        // Remove paths with just background fill (no actual icon content)
        $cleaned = (string) preg_replace('/<path\s+d=["\']M\s*0[^"\']*[hH]\s*[0-9]+[^"\']*[vV]\s*[0-9]+[^"\']*[Hh]\s*0[^"\']*[zZ]["\'][^>]*\/?>/i', '', $cleaned);
        
        // Remove background circles (common in some icons like crosshair/ads)
        // Pattern: circles that cover most/all of the viewport (radius >= 50% of viewBox)
        // Match circles with radius >= 100 (assuming 256x256 viewBox) or similar large circles
        $cleaned = (string) preg_replace('/<circle\s+[^>]*cx=["\']128["\'][^>]*cy=["\']128["\'][^>]*r=["\'](12[0-9]|1[3-9][0-9]|[2-9][0-9]{2})["\'][^>]*\/?>/i', '', $cleaned);
        // Also match circles centered at 50% (256/2 = 128, 512/2 = 256, etc.)
        $cleaned = (string) preg_replace('/<circle\s+[^>]*cx=["\']256["\'][^>]*cy=["\']256["\'][^>]*r=["\'](2[4-9][0-9]|[3-9][0-9]{2})["\'][^>]*\/?>/i', '', $cleaned);
        $cleaned = (string) preg_replace('/<circle\s+[^>]*cx=["\']512["\'][^>]*cy=["\']512["\'][^>]*r=["\'](4[8-9][0-9]|[5-9][0-9]{2})["\'][^>]*\/?>/i', '', $cleaned);
        
        // Remove circles with stroke that are clearly backgrounds (large radius, white/black stroke)
        $cleaned = (string) preg_replace('/<circle\s+[^>]*stroke=["\']#?fff(fff)?["\'][^>]*r=["\'](10[0-9]|1[1-9][0-9]|[2-9][0-9]{2})["\'][^>]*\/?>/i', '', $cleaned);
        $cleaned = (string) preg_replace('/<circle\s+[^>]*stroke=["\']white["\'][^>]*r=["\'](10[0-9]|1[1-9][0-9]|[2-9][0-9]{2})["\'][^>]*\/?>/i', '', $cleaned);

        // Replace hardcoded fill colors with currentColor
        $cleaned = (string) preg_replace('/fill=["\']#?fff(fff)?["\']/i', 'fill="currentColor"', $cleaned);
        $cleaned = (string) preg_replace('/fill=["\']white["\']/i', 'fill="currentColor"', $cleaned);
        $cleaned = (string) preg_replace('/fill=["\']#000(000)?["\']/i', 'fill="currentColor"', $cleaned);
        $cleaned = (string) preg_replace('/fill=["\']black["\']/i', 'fill="currentColor"', $cleaned);
        
        // Replace hardcoded stroke colors with currentColor (for non-background elements)
        $cleaned = (string) preg_replace('/stroke=["\']#?fff(fff)?["\']/i', 'stroke="currentColor"', $cleaned);
        $cleaned = (string) preg_replace('/stroke=["\']white["\']/i', 'stroke="currentColor"', $cleaned);
        $cleaned = (string) preg_replace('/stroke=["\']#000(000)?["\']/i', 'stroke="currentColor"', $cleaned);
        $cleaned = (string) preg_replace('/stroke=["\']black["\']/i', 'stroke="currentColor"', $cleaned);
        
        // Remove any fill="none" on main paths (they should have fill)
        $cleaned = (string) preg_replace('/<path\s+([^>]*)\s+fill=["\']none["\']([^>]*)>/', '<path $1$2>', $cleaned);

        // Ensure SVG has viewBox attribute for proper scaling
        if (strpos($cleaned, 'viewBox') === false) {
            // Try to extract width and height to create viewBox
            if (preg_match('/width="([\d.]+)"/', $cleaned, $widthMatch)
                && preg_match('/height="([\d.]+)"/', $cleaned, $heightMatch)) {
                $width = $widthMatch[1];
                $height = $heightMatch[1];
                $cleaned = (string) preg_replace(
                    '/<svg/',
                    sprintf('<svg viewBox="0 0 %s %s"', $width, $height),
                    $cleaned,
                    1
                );
            } else {
                // Default viewBox for game-icons.net (usually 512x512)
                $cleaned = (string) preg_replace('/<svg/', '<svg viewBox="0 0 512 512"', $cleaned, 1);
            }
        }

        // Remove width and height attributes to allow CSS sizing
        $cleaned = (string) preg_replace('/<svg([^>]*)\s+(width|height)="[^"]*"/', '<svg$1', $cleaned);
        $cleaned = (string) preg_replace('/<svg([^>]*)\s+(width|height)="[^"]*"/', '<svg$1', $cleaned); // Run twice to remove both

        // Ensure all paths have fill="currentColor" if they don't have a fill attribute
        $cleaned = (string) preg_replace('/<path\s+((?!fill=)[^>]*?)>/', '<path fill="currentColor" $1>', $cleaned);

        // Trim whitespace
        return trim($cleaned);
    }
}

