<?php

namespace App\Command;

use App\Repository\RuleIconRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:export-icons-to-fixtures',
    description: 'Export icons from database to fixture file format'
)]
class ExportIconsToFixturesCommand extends Command
{
    public function __construct(
        private readonly RuleIconRepository $ruleIconRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Exporting Icons to Fixtures');

        $icons = $this->ruleIconRepository->findAll();

        if (empty($icons)) {
            $io->warning('No icons found in database. Run "make download-icons" first.');
            return Command::FAILURE;
        }

        $io->text(sprintf('Found %d icons to export.', count($icons)));

        // Group icons by category for better organization
        $iconsByCategory = [];
        foreach ($icons as $icon) {
            $category = $icon->getCategory();
            if (!isset($iconsByCategory[$category])) {
                $iconsByCategory[$category] = [];
            }
            $iconsByCategory[$category][] = $icon;
        }

        // Generate fixture code
        $fixtureCode = $this->generateFixtureCode($iconsByCategory);

        // Write to file
        $fixturePath = __DIR__ . '/../DataFixtures/RuleIconFixtures.php';

        // Generate full fixture file
        $finalCode = $this->getDefaultTemplate();
        $finalCode = str_replace('{{ICONS_DATA}}', $fixtureCode, $finalCode);

        file_put_contents($fixturePath, $finalCode);

        $io->success([
            sprintf('Exported %d icons to fixtures', count($icons)),
            sprintf('File: %s', $fixturePath),
        ]);

        $io->note('Run "make fixtures" to load icons from fixtures instead of downloading.');

        return Command::SUCCESS;
    }

    private function generateFixtureCode(array $iconsByCategory): string
    {
        $code = "return [\n";

        foreach ($iconsByCategory as $category => $icons) {
            $code .= "            // ===== " . strtoupper($category) . " =====\n";
            
            foreach ($icons as $icon) {
                $code .= "            [\n";
                $code .= "                'identifier' => " . var_export($icon->getIdentifier(), true) . ",\n";
                $code .= "                'category' => " . var_export($icon->getCategory(), true) . ",\n";
                $code .= "                'displayName' => " . var_export($icon->getDisplayName(), true) . ",\n";
                $code .= "                'svg' => " . var_export($icon->getSvgContent(), true) . ",\n";
                
                $tags = $icon->getTags();
                if ($tags) {
                    $code .= "                'tags' => " . var_export($tags, true) . ",\n";
                } else {
                    $code .= "                'tags' => null,\n";
                }
                
                $color = $icon->getColor();
                if ($color) {
                    $code .= "                'color' => " . var_export($color, true) . ",\n";
                }
                
                $license = $icon->getLicense();
                if ($license) {
                    $code .= "                'license' => " . var_export($license, true) . ",\n";
                }
                
                $source = $icon->getSource();
                if ($source) {
                    $code .= "                'source' => " . var_export($source, true) . ",\n";
                }
                
                $code .= "            ],\n";
            }
            
            $code .= "\n";
        }

        $code .= "        ];";

        return $code;
    }

    private function getDefaultTemplate(): string
    {
        return <<<'PHP'
<?php

namespace App\DataFixtures;

use App\Entity\RuleIcon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RuleIconFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            TarotCardFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $icons = $this->getIconsData();

        foreach ($icons as $iconData) {
            $icon = new RuleIcon();
            $icon->setIdentifier($iconData['identifier']);
            $icon->setCategory($iconData['category']);
            $icon->setDisplayName($iconData['displayName']);
            $icon->setSvgContent($iconData['svg']);
            $icon->setTags($iconData['tags'] ?? null);
            if (isset($iconData['color'])) {
                $icon->setColor($iconData['color']);
            }
            $icon->setLicense($iconData['license'] ?? 'CC BY 3.0');
            $icon->setSource($iconData['source'] ?? 'game-icons.net');

            $manager->persist($icon);

            // Create reference for linking to rules
            $this->addReference('icon_' . $iconData['identifier'], $icon);
        }

        $manager->flush();

        echo sprintf("\n✓ Created %d rule icons\n", count($icons));
    }

    private function getIconsData(): array
    {
        {{ICONS_DATA}}
    }
}
PHP;
    }
}

