<?php

use React\EventLoop\Factory;
use Toalett\React\Stream\StreamAdapter;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/classes.php';

$loop = Factory::create();

$stream = new StreamAdapter(new Joker(), $loop);
$stream->on('data', fn(string $joke) => print($joke . PHP_EOL));

print(<<<EOF
This program demonstrates an example of an endless source.
The stream presents a joke at random intervals (0.0 - 5.0 seconds).
Press CTRL+C to stop.


EOF
);

$loop->run();
