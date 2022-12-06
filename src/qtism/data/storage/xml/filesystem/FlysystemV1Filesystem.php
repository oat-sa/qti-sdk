<?php

namespace qtism\data\storage\xml\filesystem;

use Exception;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;

class FlysystemV1Filesystem implements FilesystemInterface
{
    protected Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public static function local(string $path = '/'): self
    {
        return new self(new Filesystem(new Local($path)));
    }

    public function write(string $url, string $content): bool
    {
        try {
            return $this->filesystem->put($url, $content);
        } catch (Exception $e) {
            throw new FilesystemException("Could not write to file '${url}'", $e->getCode(), $e);
        }
    }

    public function read(string $url)
    {
        try {
            return $this->filesystem->read($url);
        } catch (FileNotFoundException $e) {
            throw new FilesystemException("Could not read file '${url}'", $e->getCode(), $e);
        }
    }
}