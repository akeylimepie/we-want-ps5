<?php

class Cache
{
    private string $dir = __DIR__.'/../var';

    private string $file;

    public function __construct()
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir);
        }

        $this->file = "{$this->dir}/cache.json";
    }

    public function pull(): array
    {
        if (is_file($this->file)) {
            $raw = file_get_contents($this->file);

            return json_decode($raw, true) ?? [];
        } else {
            return [];
        }
    }

    public function push(array $data): void
    {
        file_put_contents($this->file, json_encode($data));
    }
}