<?php

use JoopSchilder\React\Stream\NonBlockingInput\ReadableNonBlockingInputStream;
use React\EventLoop\Factory;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/demo_classes.php';

$input = new DemoJokeGenerator();

$loop = Factory::create();
$stream = new ReadableNonBlockingInputStream($input, $loop);
$stream->on('data', fn(Joke $joke) => print($joke . PHP_EOL));
$loop->addPeriodicTimer(0.2, fn() => print('.' . PHP_EOL));
$loop->run();

