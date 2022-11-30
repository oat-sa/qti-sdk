<?php

namespace qtism\data\storage\xml\filesystem;

use RuntimeException;

class FilesystemFactory
{
    public static function local(string $path = '/'): FilesystemInterface
    {
        // Check for Flysystem v1
        if (self::isFlysystemV1Installed()) {
            return FlysystemV1Filesystem::local($path);
        }

        if (self::isFlysystemV2Installed()) {
            return FlysystemV2Filesystem::local($path);
        }

        throw new RuntimeException('Local filesystem could not be initialized.  Please install Flysystem or provide your own FilesystemInterface');
    }

    public static function isFlysystemV1Installed(): bool
    {
        return class_exists('League\\Flysystem\\Filesystem') && class_exists('League\\Flysystem\\Adapter\\Local');
    }

    public static function isFlysystemV2Installed(): bool
    {
        return class_exists('League\\Flysystem\\Filesystem') && class_exists('League\\Flysystem\\Local\\LocalFilesystemAdapter');
    }
}