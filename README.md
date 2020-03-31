# Feedback Rater

## About

Prediction rating of feedback based on ML. 


## 1. Installation ##

Add the `tmitry/feedback-rater` package to your `require` section in the `composer.json` file.

``` bash
$ composer require tmitry/feedback-rater
```

Save your  dataset in `datasets/feedbacks.csv` (if you need).

## 2. Usage ##

### Console ###

``` bash
$ bin/console list
$ bin/console <command> --help
```

Compare accuracy of different classifiers and tokenizers:
``` bash
$ bin/console app:compare
```
```
 8/8 [============================] 100%
+----------------+-------------------+------------------+
| Tokenizer      | Classifier        | Accuracy         |
+----------------+-------------------+------------------+
| WordTokenizer  | NaiveBayes        | 0.91814946619217 |
| WordTokenizer  | SVC               | 0.97508896797153 |
| WordTokenizer  | KNearestNeighbors | 0.94661921708185 |
| NGramTokenizer | NaiveBayes        | 0.91814946619217 |
| NGramTokenizer | SVC               | 0.97508896797153 |
| NGramTokenizer | KNearestNeighbors | 0.94661921708185 |
+----------------+-------------------+------------------+
```

Train model:
``` bash
$ bin/console app:train
Model trained, file: model.wordtokenizer_naivebayes
```

Train specific model:
``` bash
$ bin/console app:train --help
Usage:
  app:train [options]

Options:
  -t, --tokenizer[=TOKENIZER]    WordTokenizer|NGramTokenizer [default: "WordTokenizer"]
  -c, --classifier[=CLASSIFIER]  NaiveBayes|SVC|KNearestNeighbors [default: "NaiveBayes"]
```
Allowed [classifiers](https://php-ml.readthedocs.io/en/latest/machine-learning/classification/svc/) and [tokenizers](https://php-ml.readthedocs.io/en/latest/machine-learning/feature-extraction/token-count-vectorizer/)

``` bash
$ bin/console app:train -t NGramTokenizer -c SVC
Model trained, file: model.ngramtokenizer_svc
```

Rate feedback:
``` bash
$ bin/console app:rate 'Delivered as promised. So far works great!'
Rating: positive
$ bin/console app:rate 'Package arrived 24 late. Bad experience'
Rating: negative
```

### API ###
API is based on [API Platform](https://api-platform.com/). Start the built-in PHP server:
``` bash
$ php -S 127.0.0.1:8000 -t public
```

``` bash
$ curl -X POST "http://127.0.0.1:8000/api/ratings" -H "accept: application/ld+json" -H "Content-Type: application/ld+json" -d "{\"feedback\":\"Delivered as promised\\rSo far works great!\"}"
```
```
{
  "@context": "/api/contexts/Rating",
  "@id": "/api/ratings/Delivered%2520as%2520promised%250DSo%2520far%2520works%2520great%2521",
  "@type": "Rating",
  "feedback": "Delivered as promised\rSo far works great!",
  "ratingValue": "positive",
  "classifier": "NaiveBayes",
  "tokenizer": "WordTokenizer"
}
```