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

use qtism\runtime\storage\driver\exception\DriverDeletionException;
use qtism\runtime\storage\driver\exception\DriverReadingException;
use qtism\runtime\storage\driver\exception\DriverWritingException;

interface StorageDriverInterface
{
    /**
     * @throws DriverReadingException
     */
    public function read(string $key): string;

    /**
     * @throws DriverWritingException
     */
    public function write(string $key, string $storedValue);

    public function exists(string $key): bool;

    /**
     * @throws DriverDeletionException
     */
    public function delete(string $key);
}
