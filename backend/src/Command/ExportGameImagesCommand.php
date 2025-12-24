<?php

namespace App\Command;

use App\Repository\GameRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:export-game-images',
    description: 'Export game images to PHP array format for fixtures',
)]
class ExportGameImagesCommand extends Command
{
    public function __construct(
        private readonly GameRepository $gameRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $games = $this->gameRepository->findAll();
        $imageMap = [];

        foreach ($games as $game) {
            if ($game->getImage() !== null) {
                $imageMap[$game->getName()] = $game->getImage();
            }
        }

        // Output PHP array format
        $io->title('Game Images Export');
        $io->writeln('Copy this to backend/src/DataFixtures/GameImagesData.php:');
        $io->newLine();

        echo "<?php\n\n";
        echo "namespace App\\DataFixtures;\n\n";
        echo "class GameImagesData\n";
        echo "{\n";
        echo "    public static function getImages(): array\n";
        echo "    {\n";
        echo "        return [\n";

        foreach ($imageMap as $name => $image) {
            // Truncate to first 100 chars for preview
            $preview = substr($image, 0, 100);
            echo '            ' . var_export($name, true) . " => \n";
            echo '                // Image data: ' . strlen($image) . " bytes\n";
            echo '                ' . var_export($image, true) . ",\n";
        }

        echo "        ];\n";
        echo "    }\n";
        echo "}\n";

        $io->newLine();
        $io->success('Exported ' . count($imageMap) . ' game images!');
        $io->note('File will be created at: backend/src/DataFixtures/GameImagesData.php');

        return Command::SUCCESS;
    }
}
