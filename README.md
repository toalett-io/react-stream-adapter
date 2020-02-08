# ```reactphp-input-stream```
## What is this?
A compact library that can wrap a non-blocking input to behave like a [stream](https://reactphp.org/stream/) in a [ReactPHP EventLoop](https://reactphp.org/event-loop/).

## How do I use this?
Check out the `examples` folder. It contains a very basic example of a non-blocking input implementation, as well
as a very (_very_) lame joke generator.

## Why is this useful?
I needed to respond to incoming AMQP messages in my event loop and I was feeling adventurous.  
Admittedly, I could have just used a periodic timer directly (that is what this library uses under the hood), but where's the fun in that?
  
I hear you ask: "_Where's the code that deals with the AMQP messages_?"  
While I was writing this, I felt like it would be useful to 
separate the logic of dealing with input in a stream-like manner from the logic that
deals with AMQP messages.   
This means you can reuse this library to map practically anything to a readable stream.  
An extra-lame example of this can be found in `examples/jokes_example.php`.  
  
When the code for AMQP consumption as stream is finished, I will link it here.

## Where are the unit tests?
_Errrrrr..._

## How do I install this?
This should do it [once it's available on Packagist](https://packagist.org/packages/joopschilder/):
```bash
composer require joopschilder/reactphp-input-stream
```

## Mandatory block of example code
```php
// Say, we have an event loop...
$loop = Factory::create();

// And say, we have a non-blocking input called $input...
$input = new DemoNonBlockingInput();

// Then we can create a ReadableStream from it like so:
$stream = new ReadableNonBlockingInputStream($input, $loop);

// If your 'select()' method takes a long time to execute, or you just don't
// feel like polling the input availability that often, you can 
// set a custom polling interval by supplying an instance of PollingInterval
// as the third constructor parameter:
$lowPollingStream = new ReadableNonBlockingInputStream($input, $loop, new PollingInterval(5));

// Of course, the stream emits all expected events (except end)
$stream->on('data', fn() => print('m'));
$stream->on('error', fn() => print('e'));
$stream->on('close', fn() => print('c'));

// If you know what data your input returns, you may type-hint it:
$stream->on('data', fn(Joke $joke) => print($joke . PHP_EOL));

// Add a periodic timer for demonstration purposes
$loop->addPeriodicTimer(0.2, fn() => print('.'));

// And kick 'er off.
$loop->run();
```
