<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace qtism\runtime\storage\driver;

use qtism\data\storage\xml\filesystem\FilesystemException;
use qtism\data\storage\xml\filesystem\FilesystemFactory;
use qtism\data\storage\xml\filesystem\FilesystemInterface;
use qtism\runtime\storage\driver\exception\DriverReadingException;
use qtism\runtime\storage\driver\exception\DriverWritingException;

class FilesystemLocalDriver implements StorageDriverInterface
{
    private FilesystemInterface $fileSystem;

    public function __construct(string $path = '')
    {
        $this->fileSystem = FilesystemFactory::local((empty($path) === false) ? $path : sys_get_temp_dir());
    }

    /**
     * @inheritdoc
     */
    public function read(string $key): string
    {
        try {
            $content = $this->fileSystem->read($key);
            if (is_bool($content)) {
                throw new DriverReadingException('Can\'t read from ' . $key);
            }
        } catch (FilesystemException $e) {
            throw new DriverReadingException($e->getMessage(), $e->getCode(), $e);
        }

        return $content;
    }

    /**
     * @inheritdoc
     */
    public function write(string $key, string $storedValue)
    {
        try {
            $writingResult = $this->fileSystem->write($key, $storedValue);
            if (false === $writingResult) {
                throw new DriverWritingException('Can\'t write to ' . $key);
            }
        } catch (FilesystemException $e) {
            throw new DriverWritingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function exists(string $key): bool
    {
        try {
            $this->read($key);
            return true;
        } catch (DriverReadingException) {
            return false;
        }
    }

    public function delete(string $key)
    {
        throw new \RuntimeException('method not implemented');
    }
}
