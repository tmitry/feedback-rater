<?php
namespace App\Entity;

final class Rating
{
    /**
     * @var string
     */
    private $feedback = "";

    /**
     * @var string
     */
    private $ratingValue = "";

    /**
     * @var string
     */
    private $classifier = "NaiveBayes";

    /**
     * @var string
     */
    private $tokenizer = "WordTokenizer";

    /**
     * @return string
     */
    public function getFeedback(): string
    {
        return $this->feedback;
    }

    /**
     * @param string $feedback
     */
    public function setFeedback(string $feedback): void
    {
        $this->feedback = $feedback;
    }

    /**
     * @return string
     */
    public function getRatingValue(): string
    {
        return $this->ratingValue;
    }

    /**
     * @param string $ratingValue
     */
    public function setRatingValue(string $ratingValue): void
    {
        $this->ratingValue = $ratingValue;
    }

    /**
     * @return string
     */
    public function getClassifier(): string
    {
        return $this->classifier;
    }

    /**
     * @param string $classifier
     */
    public function setClassifier(string $classifier): void
    {
        $this->classifier = $classifier;
    }

    /**
     * @return string
     */
    public function getTokenizer(): string
    {
        return $this->tokenizer;
    }

    /**
     * @param string $tokenizer
     */
    public function setTokenizer(string $tokenizer): void
    {
        $this->tokenizer = $tokenizer;
    }
}