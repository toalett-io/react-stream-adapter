<?php

use React\EventLoop\Factory;
use Toalett\React\Stream\StreamAdapter;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/classes.php';

$loop = Factory::create();

$stream = new StreamAdapter(new ReachesEofIn5Iterations(), $loop, 0.1);
$stream->on('data', fn(string $s) => printf('Data received: %s.%s', $s, PHP_EOL));
$stream->on('end', fn() => print('Stream reached eof.' . PHP_EOL));
$stream->on('close', fn() => print('Stream closed.' . PHP_EOL));

print(<<<EOF
This program demonstrates an example of a source that reaches EOF after 5 lines.

The stream adapter reads eagerly from the source: data is emitted as long as 
select() on the source returns a non-null value. This means that all lines from
the source in this example ar read at once. If you want the adapter to read
at most one unit (message) from the source, you should probably be using a
periodic timer directly, or use time mechanics as used by the other examples.


EOF
);

$loop->run();
