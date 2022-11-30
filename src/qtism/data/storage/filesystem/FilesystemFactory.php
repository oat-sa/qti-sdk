<?php

namespace qtism\data\storage\filesystem;

use RuntimeException;
use Error;

class FilesystemFactory
{
    public static function local(string $path = '/'): FilesystemInterface
    {
        try {
            return FlysystemV2Filesystem::local($path);
        } catch (Error $ex1) {
        }

        try {
            return FlysystemV1Filesystem::local($path);
        } catch (Error $ex) {
            throw new RuntimeException('Local filesystem could not be initialized.  Please install Flysystem or provide your own FilesystemInterface');
        }
    }

    public static function isFlysystemV1Installed(): bool
    {
        return class_exists('League\\Flysystem\\Filesystem') && class_exists('League\\Flysystem\\Adapter\\Local');
    }

    public static function isFlysystemV2V3Installed(): bool
    {
        return class_exists('League\\Flysystem\\Filesystem') && class_exists('League\\Flysystem\\Local\\LocalFilesystemAdapter');
    }
}
