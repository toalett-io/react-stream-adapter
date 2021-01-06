# 🚽 Toalett

Welcome to Toalett, a humble initiative. Toalett is the Norwegian word for toilet 💩.

## What is `toalett/react-stream-adapter`?

It is a library that allows any datasource to be used as a stream with ReactPHP. It is very small - there
is [one interface](src/Source.php), [one class](src/StreamAdapter.php)
and [one trait](src/EndlessTrait.php). Its only dependency is `react/stream`.

The [`StreamAdapter`](src/StreamAdapter.php) takes an implementation of the [`Source`](src/Source.php) interface and
makes it approachable as a [stream](https://reactphp.org/stream/) in applications using
an [event loop](https://reactphp.org/event-loop/).

## Installation

It is available on [Packagist](https://packagist.org/packages/toalett/):

```bash
composer require toalett/react-stream-adapter
```

## Motivation

I was working on a project that required an application to respond to AMQP messages in a non-blocking way. The
application made use of an event loop. Initially I used a periodic timer with a callback, but as the application grew
this became a cluttered mess. It slowly started to feel more natural to treat the message queue as a stream. This makes
sense if you think about it:

> In computer science, a stream is a sequence of data elements made available over time. A stream can be thought of as items
> on a conveyor belt being processed one at a time rather than in large batches.
>
> &mdash; <cite> [Stream (computing) on Wikipedia](https://en.wikipedia.org/wiki/Stream_(computing)) </cite>

This definition suits a message queue.

In the project I mentioned earlier, I use this library to poll an AMQP queue every 10 seconds. This keeps my load low
and allows me to do other things in the meantime. This abstraction turned out really useful, so I thought that others
might enjoy it too.

## How do I use this?

There are only three components to worry about, and one of them is optional! The main components are
the [`Source`](src/Source.php) interface and the [`StreamAdapter`](src/StreamAdapter.php) class. The optional component
is the [`EndlessTrait`](src/EndlessTrait.php), which can be used in an endless source.

The steps you need to take to use this library are as follows:

1. Define a class that is able to generate or provide some data. It must implement the [`Source`](src/Source.php)
   interface.
2. The `select()` method is called periodically. This is where you return your next piece of data. Make sure
   the `select()` method returns anything that is not `null` when data is available (anything goes) or `null`
   when there is none. You may add a typehint to your implementation of `select()` such as `select(): ?string`
   or `select(): ?MyData` for improved clarity.
3. The interface also specifies the `close(): void` and `eof(): bool` methods. In an endless (infinite)
   stream, `close()` may be left empty and `eof()` should return false (EOF is never reached).
   The [`EndlessTrait`](src/EndlessTrait.php)
   provides these implementations.
4. Use the [`StreamAdapter`](src/StreamAdapter.php) to attach your [`Source`](src/Source.php) to the loop.
5. Interact with the adapter as if it were any other `ReadableInputStream`.

_Note:_ This library uses polling under the hood. The default polling interval is 0.5 seconds, though if checking for
data is an intensive operation, you might want to increase the interval a bit to prevent slowdowns. This is a tradeoff
between responsiveness and load. Custom intervals can be set by passing them as a third argument to
the [`StreamAdapter`](src/StreamAdapter.php) constructor.

_Note:_ The [`StreamAdapter`](src/StreamAdapter.php) reads data eagerly from the source - it won't stop reading until
there is nothing left to read. This prevents congestion when high polling intervals are used but it might block
execution for a while when there is a lot of data to be read or if your `select()` routine takes some time.

```php
$loop = Factory::create();

$source = new MySource(); // implements Source
$stream = new StreamAdapter($source, $loop);
$stream->on('data', function(MyData $data) {
    /* do something with data */
});

$loop->run();
```

Check out the [examples](examples) folder for some simple implementations.

## Questions

__Q__: _Where is the code that deals with AMQP messages_?  
__A__: It will be released in a separate package, but it needs some work before it can be published.

__Q__: _Where are the tests_?  
__A__: There is only one class, and it is mostly based on the `ReadableResourceStream` from `react/stream`. Tests might
be added later, but as of now, I don't really see the value. Feel free to create an issue if this bothers you!
