<?php

namespace JoopSchilder\React\Stream\NonBlockingInput\ValueObject;

use InvalidArgumentException;

final class PollingInterval
{
	private const DEFAULT_INTERVAL = 0.05;

	private float $interval;


	public function __construct(float $interval = self::DEFAULT_INTERVAL)
	{
		if ($interval < 0.0) {
			throw new InvalidArgumentException('Interval must be greater than 0');
		}

		$this->interval = $interval;
	}


	public function getInterval(): float
	{
		return $this->interval;
	}

}
