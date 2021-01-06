<?php

namespace Toalett\React\Stream;

trait EndlessTrait
{
    public function close(): void
    {
    }

    public function eof(): bool
    {
        return false;
    }
}
