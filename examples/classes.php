<?php

use Toalett\React\Stream\EndlessTrait;
use Toalett\React\Stream\Source;

final class WillThrowExceptionAfter4Seconds implements Source
{
    use EndlessTrait;

    private const EMISSION_INTERVAL = 1.0;
    private const SECONDS_BEFORE_EMITTING_ERROR = 4.0;
    private float $lastEmission = 0;
    private float $openedAt = 0;

    public function open(): void
    {
        $this->openedAt = microtime(true);
    }

    public function select(): ?float
    {
        $now = microtime(true);
        if ($now - $this->openedAt > self::SECONDS_BEFORE_EMITTING_ERROR) {
            throw new RuntimeException('An error has occured!');
        }
        if ($now - $this->lastEmission > self::EMISSION_INTERVAL) {
            $this->lastEmission = $now;
            return $now;
        }

        return null;
    }
}

final class Joker implements Source
{
    use EndlessTrait;

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

    public function open(): void
    {
        $this->scheduleNextJoke();
    }

    public function select(): ?string
    {
        if (microtime(true) < $this->deadline) {
            return null;
        }

        $this->scheduleNextJoke();
        return $this->jokes[array_rand($this->jokes)];
    }

    private function scheduleNextJoke(): void
    {
        $delay = mt_rand() / mt_getrandmax(); // somewhere between 0 - 1
        $this->deadline = microtime(true) + (5.0 * $delay);
    }
}

final class ReachesEofIn5Iterations implements Source
{
    private array $buffer = [
        'line 1',
        'line 2',
        'line 3',
        'line 4',
        'line 5',
    ];

    public function open(): void
    {
    }

    public function select(): ?string
    {
        return array_shift($this->buffer);
    }

    public function close(): void
    {
    }

    public function eof(): bool
    {
        return count($this->buffer) === 0;
    }
}
