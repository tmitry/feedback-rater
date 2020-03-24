<?php
namespace App\Controller;

use App\Entity\Rating;
use App\Service\MLManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class GetFeedbackRating extends AbstractController
{
    private $mlManager;

    public function __construct(MLManager $mlManager)
    {
        $this->mlManager = $mlManager;
    }

    public function __invoke(Rating $data): Rating
    {
        $this->mlManager->setTokenizer($data->getTokenizer());
        $this->mlManager->setClassifier($data->getClassifier());

        $ratingValue = $this->mlManager->rate($data->getFeedback());

        $data->setRatingValue($ratingValue);

        return $data;
    }
}