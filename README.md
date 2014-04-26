Jason Roman's PHP Flot Class
========
[![Build Status](https://travis-ci.org/jasonroman/flot.svg?branch=master)](https://travis-ci.org/jasonroman/flot)

This is a class that transforms PHP arrays of series data into a JSON format that Flot can understand. It supports line/bar charts, pie charts, horizontal/vertical orientation, and time series data. It also supports single or multiple series.

To load as a service in a Symfony bundle, see my <a href="https://github.com/jasonroman/flot">jasonroman/flot-bundle</a> package.

## Usage

```php
// convert to Flot JSON data from PHP arrays
use JasonRoman\Flot\Flot;

$flot = new Flot;

$flotData = $flot->convert($data);
$flotData = $flot->convert($data, 'horizontal');
$flotData = $flot->convert($data, 'vertical', $datetime = true);
$flotData = $flot->convert($pieData);
```

See the comments in the class for more examples of the various forms of array $data that can be passed to the convert() function.
