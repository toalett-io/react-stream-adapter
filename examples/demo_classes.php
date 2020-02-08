<?php

use JoopSchilder\React\Stream\NonBlockingInput\NonBlockingInputInterface;

/**
 * Class DemoNonBlockingInput
 * Provides an implementation of a non-blocking input that generates
 * some data every second. After a lifetime of 4 seconds, an exception
 * is thrown to show how the stream handles exceptions from the input.
 */
final class DemoNonBlockingInput implements NonBlockingInputInterface
{
	private const DATA_AVAILABLE_INTERVAL_S = 1.0;
	private const ERROR_AFTER_S = 4.0;

	private float $lastEmission = 0;

	private float $initialEmission = 0;


	public function open(): void
	{
		$this->initialEmission = microtime(true);
	}


	public function select(): ?object
	{
		$now = microtime(true);
		if ($now - $this->initialEmission > self::ERROR_AFTER_S) {
			throw new Exception('Oh no!');
		}
		if ($now - $this->lastEmission > self::DATA_AVAILABLE_INTERVAL_S) {
			$this->lastEmission = $now;

			return new stdClass();
		}

		return null;
	}


	public function close(): void
	{
	}

}

/**
 * Class Joke
 * Used as a payload for the DemoJokeGenerator.
 * @see DemoJokeGenerator
 */
final class Joke
{
	private string $joke;


	public function __construct(string $joke)
	{
		$this->joke = $joke;
	}


	public function __toString()
	{
		return $this->joke;
	}

}

/**
 * Class DemoJokeGenerator
 * Generates a random joke at a random interval (0 - 2 seconds).
 */
final class DemoJokeGenerator implements NonBlockingInputInterface
{
	private float $lastEmission = 0;

	private float $deadline = 0;

	private array $jokes = [
		'What did the Buddhist ask the hot dog vendor? - Make me one with everything.',
		'You know why you never see elephants hiding up in trees? - Because they’re really good at it.',
		'What is red and smells like blue paint? - Red paint.',
		'A dyslexic man walks into a bra.',
		'Where does the General keep his armies? - In his sleevies!',
		'What do you call bears with no ears? - B',
		'Why dont blind people skydive? - Because it scares the crap out of their dogs.',
	];


	private function scheduleNextJoke()
	{
		$this->lastEmission = microtime(true);
		$delay = mt_rand() / mt_getrandmax();
		$this->deadline = $this->lastEmission + (2.0 * $delay);
	}


	public function open(): void
	{
		$this->scheduleNextJoke();
	}


	public function select(): ?object
	{
		$now = microtime(true);
		if ($now > $this->deadline) {
			$this->scheduleNextJoke();

			return new Joke($this->jokes[array_rand($this->jokes)]);
		}

		return null;
	}


	public function close(): void
	{
	}

}
