<?php

namespace Toalett\React\Stream;

interface Source
{
    public function open(): void;

    /**
     * @return mixed|null
     */
    public function select();

    public function close(): void;

    public function eof(): bool;
}
