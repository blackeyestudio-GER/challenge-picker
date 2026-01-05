<?php

namespace App\Command;

use App\Repository\RuleIconRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:delete-all-icons',
    description: 'Delete all icons from the database (use before re-downloading)'
)]
class DeleteAllIconsCommand extends Command
{
    public function __construct(
        private readonly RuleIconRepository $ruleIconRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Skip confirmation prompt');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Delete All Icons');
        $io->warning('This will delete ALL icons from the database!');

        $icons = $this->ruleIconRepository->findAll();
        $count = count($icons);

        if ($count === 0) {
            $io->info('No icons found in database.');
            return Command::SUCCESS;
        }

        $io->text(sprintf('Found %d icons to delete.', $count));

        $force = $input->getOption('force');
        $nonInteractive = !$input->isInteractive();
        
        if (!$force && !$nonInteractive && !$io->confirm('Are you sure you want to delete all icons?', false)) {
            $io->info('Cancelled.');
            return Command::SUCCESS;
        }

        foreach ($icons as $icon) {
            $this->entityManager->remove($icon);
        }

        $this->entityManager->flush();

        $io->success(sprintf('Deleted %d icons.', $count));
        $io->note('Run "make download-icons" to download fresh icons from game-icons.net');

        return Command::SUCCESS;
    }
}

