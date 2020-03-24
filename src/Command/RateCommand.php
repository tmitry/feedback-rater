<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\MLManager;

class RateCommand extends Command
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
            ->setName("app:rate")
            ->setDescription('Rates feedback')
            ->setHelp('This command rates feedback.')
            ->addArgument('feedback', InputArgument::REQUIRED, 'Feedback text')
            ->addOption('tokenizer', 't', InputOption::VALUE_OPTIONAL, implode('|', array_keys(MLManager::TOKENIZERS)), 'WordTokenizer')
            ->addOption('classifier', 'c', InputOption::VALUE_OPTIONAL, implode('|', array_keys(MLManager::CLASSIFICATIONS)), 'NaiveBayes');
    }



    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->mlManager->setTokenizer($input->getOption('tokenizer'));
        $this->mlManager->setClassifier($input->getOption('classifier'));

        $output->writeln(["Rating: " . $this->mlManager->rate($input->getArgument('feedback'))]);

        return 0;
    }
}