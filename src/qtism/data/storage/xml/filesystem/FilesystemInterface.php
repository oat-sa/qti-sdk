<?php

namespace qtism\data\storage\xml\filesystem;

interface FilesystemInterface
{
    /**
     * Read file from filesystem
     *
     * @param string $url
     * @return string|mixed|false
     * @throws FilesystemException
     */
    public function read(string $url);

    /**
     * Write content to filesystem
     *
     * @param string $url
     * @param string $content
     * @return bool
     * @throws FilesystemException
     */
    public function write(string $url, string $content): bool;
}