<?php

use JoopSchilder\React\Stream\NonBlockingInput\NonBlockingInputInterface;
use JoopSchilder\React\Stream\NonBlockingInput\PayloadInterface;

final class DemoEmptyPayload implements PayloadInterface
{
}

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


	public function select(): ?PayloadInterface
	{
		$now = microtime(true);
		if ($now - $this->initialEmission > self::ERROR_AFTER_S) {
			throw new Exception('Oh no!');
		}
		if ($now - $this->lastEmission > self::DATA_AVAILABLE_INTERVAL_S) {
			$this->lastEmission = $now;

			return new DemoEmptyPayload();
		}

		return null;
	}


	public function close(): void
	{
	}

}
