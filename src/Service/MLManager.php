<?php
declare(strict_types=1);

namespace App\Service;

use Phpml\Pipeline;
use Phpml\Estimator;
use Phpml\ModelManager;
use Symfony\Component\Console\Output\OutputInterface;
use Phpml\Dataset\CsvDataset;
use Phpml\Tokenization\WordTokenizer;
use Phpml\Tokenization\NGramTokenizer;
use Phpml\Tokenization\Tokenizer;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Metric\Accuracy;
use Phpml\Classification\NaiveBayes;
use Phpml\Classification\SVC;
use Phpml\Classification\KNearestNeighbors;
use Symfony\Component\Console\Helper\ProgressBar;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\Dataset\ArrayDataset;
use Phpml\SupportVectorMachine\Kernel;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Filesystem\Filesystem;


class MLManager
{
    /**
     * @see https://php-ml.readthedocs.io/en/latest/machine-learning/feature-extraction/token-count-vectorizer/#tokenizers
     */
    public const TOKENIZERS = [
        'WordTokenizer' => 1,
        'NGramTokenizer' => 2
    ];

    /**
     * @see https://php-ml.readthedocs.io/en/latest/machine-learning/classification/naive-bayes/
     * https://php-ml.readthedocs.io/en/latest/machine-learning/classification/svc/
     * https://php-ml.readthedocs.io/en/latest/machine-learning/classification/k-nearest-neighbors/
     */
    public const CLASSIFICATIONS = [
        'NaiveBayes' => 1,
        'SVC' => 2,
        'KNearestNeighbors' => 3
    ];


    private const DATASET_FILENAME = __DIR__ . '/../../datasets/feedbacks.csv';

    private const MODELS_FOLDER = __DIR__ . '/../../models/';

    private $fileSystem;

    private $tokenizerId;

    private $classifierId;

    public function __construct(Filesystem $fileSystem)
    {
        /* by default */
        $this->tokenizerId = 1;
        $this->classifierId = 1;

        $this->fileSystem = $fileSystem;
    }

    public function train(OutputInterface $output): void
    {
        $pipeline = $this->getPipeline();

        $dataset = new CsvDataset(self::DATASET_FILENAME, 1, false);

        $samples = [];
        foreach ($dataset->getSamples() as $sample) {
            $samples[] = $sample[0];
        }

        $pipeline->train($samples, $dataset->getTargets());

        $modelManager = new ModelManager();

        $modelFileName = $this->getModelFileName();

        $modelManager->saveToFile($pipeline, self::MODELS_FOLDER . $modelFileName);

        $output->writeln(['Model trained, file: ' . $modelFileName]);
    }

    public function rate(string $feedback): string
    {
        $modelFileName = $this->getModelFileName();

        if (!$this->fileSystem->exists([self::MODELS_FOLDER . $modelFileName])) {
            throw new \RuntimeException('Model file not found, run `app:train` before rating');
        }

        $modelManager = new ModelManager();

        $pipeline = $modelManager->restoreFromFile(self::MODELS_FOLDER . $modelFileName);

        return $pipeline->predict([$feedback])[0];
    }

    public function compare(OutputInterface $output): void
    {
        $outputProgressBar = $output->section();

        $outputComparison = $output->section();
        $outputTable = new Table($outputComparison);
        $outputTable->setHeaders(['Tokenizer', 'Classifier', 'Accuracy']);

        $progressBar = new ProgressBar($outputProgressBar, pow(count(self::TOKENIZERS), count(self::CLASSIFICATIONS)));

        $progressBar->start();

        $dataset = new CsvDataset(self::DATASET_FILENAME, 1, false);

        $samples = [];
        foreach ($dataset->getSamples() as $sample) {
            $samples[] = $sample[0];
        }

        $dataset = new ArrayDataset($samples, $dataset->getTargets());

        $split = new StratifiedRandomSplit($dataset, 0.2);

        foreach (self::TOKENIZERS as $tokenizerTitle => $tokenizerId) {
            foreach (self::CLASSIFICATIONS as $classifierTitle => $classifierId) {
                $pipeline = $this->getPipeline($tokenizerId, $classifierId);

                $pipeline->train($split->getTrainSamples(), $split->getTrainLabels());

                $predicted = $pipeline->predict($split->getTestSamples());

                $outputTable->addRow([
                    $tokenizerTitle,
                    $classifierTitle,
                    Accuracy::score($split->getTestLabels(), $predicted)
                ]);

                $progressBar->advance();
            }
        }

        $progressBar->finish();

        $outputTable->render();
    }


    public function setClassifier(string $classifier): void
    {
        if (!isset(self::CLASSIFICATIONS[$classifier])) {
            throw new \RuntimeException('Bad classifier value');
        }

        $this->classifierId = self::CLASSIFICATIONS[$classifier];
    }

    public function setTokenizer(string $tokenizer): void
    {
        if (!isset(self::TOKENIZERS[$tokenizer])) {
            throw new \RuntimeException('Bad tokenizer value');
        }

        $this->tokenizerId = self::TOKENIZERS[$tokenizer];
    }

    private function getPipeline(?int $tokenizerId = null, ?int $classifierId = null): Estimator
    {
        return new Pipeline([
            new TokenCountVectorizer($this->getTokenizer()),
            new TfIdfTransformer()
            ], $this->getClassifier($classifierId)
        );
    }


    private function getClassifier(?int $classifierId = null): Estimator
    {
        if (is_null($classifierId)) {
            $classifierId = $this->classifierId;
        }

        switch ($classifierId) {
            case 2:
                return new SVC(Kernel::LINEAR);
                break;
            case 3:
                return new KNearestNeighbors();
                break;
            default:
                return new NaiveBayes();
        }
    }

    private function getTokenizer(?int $tokenizerId = null): Tokenizer
    {
        if (is_null($tokenizerId)) {
            $tokenizerId = $this->tokenizerId;
        }

        switch ($tokenizerId) {
            case 2:
                return new NGramTokenizer(1, 3);
                break;
            default:
                return new WordTokenizer();
        }
    }

    private function getModelFileName(): string
    {
        return strtolower(
            sprintf('model.%s_%s',
                array_search($this->tokenizerId, self::TOKENIZERS),
                array_search($this->classifierId, self::CLASSIFICATIONS)
            )
        );
    }
}