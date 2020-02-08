<?php

namespace JoopSchilder\React\Stream\NonBlockingInput;

interface NonBlockingInputInterface
{

	function open(): void;


	function select(): ?object;


	function close(): void;

}
