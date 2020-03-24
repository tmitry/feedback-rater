<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\MLManager;

class CompareCommand extends Command
{
    private $mlManager;

    public function __construct(MLManager $mlManager)
    {
        $this->mlManager = $mlManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName("app:compare")
            ->setDescription('Compare existing models')
            ->setHelp('This command allows you to compare all existing models.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->mlManager->compare($output);

        return 0;
    }
}