<?php

use React\EventLoop\Factory;
use Toalett\React\Stream\StreamAdapter;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/classes.php';

$loop = Factory::create();
$loop->addPeriodicTimer(0.2, fn() => print('.'));

$stream = new StreamAdapter(new WillThrowExceptionAfter4Seconds(), $loop);
$stream->on('data', fn() => print('Data received.' . PHP_EOL));
$stream->on('error', fn(Throwable $t) => print('Error: ' . $t->getMessage() . PHP_EOL));
$stream->on('close', fn() => print('Stream closed.' . PHP_EOL));

print(<<<EOF
This program demonstrates an example of a source that fails after 4 seconds.
Press CTRL+C to stop.


EOF
);

$loop->run();
