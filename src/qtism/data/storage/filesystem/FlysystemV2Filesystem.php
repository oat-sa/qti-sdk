<?php

namespace qtism\data\storage\filesystem;

use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException as FlysystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;

class FlysystemV2Filesystem implements FilesystemInterface
{
    protected Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public static function local(string $path = '/'): self
    {
        return new self(new Filesystem(new LocalFilesystemAdapter(
            $path,
            null,
            LOCK_EX,
            LocalFilesystemAdapter::DISALLOW_LINKS,
            new ExtensionMimeTypeDetector()
        )));
    }

    public function write(string $url, string $content): bool
    {
        try {
            $this->filesystem->write($url, $content);
            return true;
        } catch (Exception $e) {
            throw new FilesystemException("Could not write to file '${url}'", $e->getCode(), $e);
        }
    }

    public function read(string $url)
    {
        try {
            return $this->filesystem->read($url);
        } catch (FlysystemException $e) {
            throw new FilesystemException("Could not read file '${url}'", $e->getCode(), $e);
        }
    }
}