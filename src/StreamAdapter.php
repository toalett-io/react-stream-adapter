<?php

namespace Toalett\React\Stream;

use Evenement\EventEmitterTrait;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Stream\ReadableResourceStream;
use React\Stream\ReadableStreamInterface;
use React\Stream\Util;
use React\Stream\WritableStreamInterface;
use RuntimeException;
use Throwable;

/**
 * @see ReadableResourceStream
 */
final class StreamAdapter implements ReadableStreamInterface
{
    use EventEmitterTrait;

    private Source $source;
    private LoopInterface $loop;
    private float $pollingInterval;
    private ?TimerInterface $timer = null;
    private bool $closed = false;
    private bool $listening = false;

    public function __construct(Source $source, LoopInterface $loop, float $pollingInterval = 0.5)
    {
        $this->source = $source;
        $this->loop = $loop;
        $this->pollingInterval = $pollingInterval;

        $this->resume();
    }

    public function isReadable(): bool
    {
        return !$this->closed;
    }

    public function pause(): void
    {
        if (!$this->listening) {
            return;
        }

        $this->source->close();
        $this->loop->cancelTimer($this->timer);
        $this->listening = false;
    }

    public function resume(): void
    {
        if ($this->listening || $this->closed) {
            return;
        }

        $this->source->open();
        $this->timer = $this->loop->addPeriodicTimer($this->pollingInterval, function () {
            while (!is_null($data = $this->read())) {
                $this->emit('data', [$data]);
            }
            if ($this->source->eof()) {
                $this->emit('end');
                $this->close();
            }
        });
        $this->listening = true;
    }

    public function pipe(WritableStreamInterface $dest, array $options = []): WritableStreamInterface
    {
        return Util::pipe($this, $dest, $options);
    }

    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->closed = true;

        $this->emit('close');
        $this->pause();
        $this->removeAllListeners();

        $this->source->close();
    }

    /**
     * @return mixed|null
     */
    private function read()
    {
        try {
            return $this->source->select();
        } catch (Throwable $t) {
            $this->emit('error', [new RuntimeException('Unable to read data from source: ' . $t->getMessage(), 0, $t)]);
            $this->close();
        }

        return null;
    }
}
