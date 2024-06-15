<?php

namespace Yui019\Hori\Util;

class StubHelper
{
    protected $content;

    public function __construct(string $path)
    {
        $this->content = file_get_contents($path);
    }

    public function replace(string $key, string $value): void
    {
        $this->content = str_replace(
            $key,
            $value,
            $this->content
        );
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function save(string $path): void
    {
        file_put_contents($path, $this->content);
    }
}
