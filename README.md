A simple crawler library for php using DOMDocument.
For educational purpose.

## Installation

```:
composer require sanzodown/simple-php-crawler
```

## Usage

```php
$crawler = new Crawler("https://www.domain.com/");

// if u need to be authentificated
$crawler->setLogin("username","password");
```

### Methods:
```php
//return an array of the DOMNode
$result = $crawler->filter(".header-logo > a");

//some lazy methods
$imgs = $crawler->getAllImages();
$links = $crawler->getAllLinks();
```
