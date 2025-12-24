<?php

namespace App\Command;

use App\Repository\CategoryRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:export-category-icons',
    description: 'Export category icons to PHP array format for fixtures',
)]
class ExportCategoryIconsCommand extends Command
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $categories = $this->categoryRepository->findAll();
        $imageMap = [];

        foreach ($categories as $category) {
            if ($category->getImage() !== null) {
                $imageMap[$category->getName()] = $category->getImage();
            }
        }

        // Output PHP array format
        $io->title('Category Icons Export');
        $io->writeln('Copy this to backend/src/DataFixtures/CategoryImagesData.php:');
        $io->newLine();

        echo "<?php\n\n";
        echo "namespace App\\DataFixtures;\n\n";
        echo "class CategoryImagesData\n";
        echo "{\n";
        echo "    public static function getImages(): array\n";
        echo "    {\n";
        echo "        return [\n";

        foreach ($imageMap as $name => $image) {
            echo '            ' . var_export($name, true) . " => \n";
            echo '                // Image data: ' . strlen($image) . " bytes\n";
            echo '                ' . var_export($image, true) . ",\n";
        }

        echo "        ];\n";
        echo "    }\n";
        echo "}\n";

        $io->newLine();
        $io->success('Exported ' . count($imageMap) . ' category icons!');
        $io->note('File will be created at: backend/src/DataFixtures/CategoryImagesData.php');

        return Command::SUCCESS;
    }
}
