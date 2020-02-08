<?php

namespace JoopSchilder\React\Stream\NonBlockingInput;

use Evenement\EventEmitter;
use JoopSchilder\React\Stream\NonBlockingInput\ValueObject\PollingInterval;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Stream\ReadableResourceStream;
use React\Stream\ReadableStreamInterface;
use React\Stream\Util;
use React\Stream\WritableStreamInterface;
use RuntimeException;
use Throwable;

/**
 * @see ReadableStreamInterface
 * @see ReadableResourceStream
 */
final class ReadableNonBlockingInputStream extends EventEmitter implements ReadableStreamInterface
{
	private NonBlockingInputInterface $input;

	private LoopInterface $loop;

	private PollingInterval $interval;

	private ?TimerInterface $periodicTimer = null;

	private bool $isClosed = false;

	private bool $isListening = false;


	/**
	 * @see ReadableStreamInterface
	 * @see ReadableResourceStream for an example
	 */
	public function __construct(NonBlockingInputInterface $input, LoopInterface $loop, ?PollingInterval $interval = null)
	{
		$this->input = $input;
		$this->loop = $loop;
		$this->interval = $interval ?? new PollingInterval();

		$this->resume();
	}


	public function isReadable()
	{
		return !$this->isClosed;
	}


	/**
	 * @see ReadableResourceStream::pause()
	 * Pause consumption of the AMQP queue but do not mark the stream as closed
	 */
	public function pause()
	{
		if (!$this->isListening) {
			return;
		}

		$this->input->close();
		$this->loop->cancelTimer($this->periodicTimer);
		$this->isListening = false;
	}


	/**
	 * @see ReadableResourceStream::resume()
	 * Register the consumer with the broker and add consumer again
	 */
	public function resume()
	{
		if ($this->isListening || $this->isClosed) {
			return;
		}

		$this->input->open();
		$this->periodicTimer = $this->loop->addPeriodicTimer(
			$this->interval->getInterval(),
			function () {
				if ($data = $this->read()) {
					$this->emit('data', [$data]);
				}
			}
		);

		$this->isListening = true;
	}


	/**
	 * @param WritableStreamInterface $dest
	 * @param array $options
	 * @return WritableStreamInterface
	 * @see ReadableResourceStream::pipe()
	 */
	public function pipe(WritableStreamInterface $dest, array $options = [])
	{
		return Util::pipe($this, $dest, $options);
	}


	/**
	 * @see ReadableResourceStream::close()
	 */
	public function close()
	{
		if ($this->isClosed) {
			return;
		}

		$this->isClosed = true;

		$this->emit('close');
		$this->pause();
		$this->removeAllListeners();

		$this->input->close();
	}


	private function read(): ?object
	{
		try {
			return $this->input->select();
		} catch (Throwable $t) {
			$this->emit('error', [
				new RuntimeException('Unable to read data from input: ' . $t->getMessage(), 0, $t),
			]);
			$this->close();
		}

		return null;
	}

}
