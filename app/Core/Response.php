<?php

declare(strict_types=1);

namespace CoreNewspaper\Core;

final class Response
{
    public function __construct(
        private int $status = 200,
        private array $headers = [],
        private string $body = ''
    ) {
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value, replace: true);
        }
        echo $this->body;
    }
}
