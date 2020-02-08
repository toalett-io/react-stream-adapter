<?php

namespace JoopSchilder\React\Stream\NonBlockingInput;

interface NonBlockingInputInterface
{

	function open(): void;


	function select(): ?PayloadInterface;
	

	function close(): void;

}
