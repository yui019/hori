<?php

namespace Yui019\Hori\Util;

class StubHelper
{
    protected $content;

    public function __construct($path)
    {
        $this->content = file_get_contents($path);
    }

    public function replace($key, $value)
    {
        $this->content = str_replace(
            $key,
            $value,
            $this->content
        );
    }

    public function getContent()
    {
        return $this->content;
    }

    public function save($path)
    {
        file_put_contents($path, $this->content);
    }
}
