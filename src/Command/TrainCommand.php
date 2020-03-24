<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\MLManager;

class TrainCommand extends Command
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
            ->setName("app:train")
            ->setDescription('Trains new model')
            ->setHelp('This command allows you to start training new model or models.')
            ->addOption('tokenizer', 't', InputOption::VALUE_OPTIONAL, implode('|', array_keys(MLManager::TOKENIZERS)), 'WordTokenizer')
            ->addOption('classifier', 'c', InputOption::VALUE_OPTIONAL, implode('|', array_keys(MLManager::CLASSIFICATIONS)), 'NaiveBayes');
    }



    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->mlManager->setTokenizer($input->getOption('tokenizer'));
        $this->mlManager->setClassifier($input->getOption('classifier'));

        $this->mlManager->train($output);

        return 0;
    }
}