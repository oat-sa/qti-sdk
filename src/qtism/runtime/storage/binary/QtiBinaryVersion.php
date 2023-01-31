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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\storage\binary;

use qtism\common\storage\BinaryStreamAccessException;

/**
 * Class QtiBinaryVersion
 */
class QtiBinaryVersion
{
    /**
     * The QTI binary data version number.
     */
    public const CURRENT_VERSION = self::VERSION_ROUTE_COUNT_INTEGER;

    /**
     * The QTI Sdk branch to select behaviour of the binary storage.
     * 'M' denotes Master. 'L' denotes Legacy.
     */
    public const CURRENT_BRANCH = 'M';

    /**
     * These constants make the different versions a bit more self-explanatory.
     */
    public const VERSION_ROUTE_COUNT_INTEGER = 13;
    public const VERSION_VARIABLE_WITH_DEFAULT_VALUE_INITIALIZATION_FLAG = 12;
    public const VERSION_VARIABLE_COUNT_INTEGER = 11;
    public const VERSION_FIRST_MASTER = 10;
    public const VERSION_POSITION_INTEGER = 9;
    public const VERSION_ALWAYS_ALLOW_JUMPS = 8;
    public const VERSION_TRACK_PATH = 7;
    public const VERSION_FORCE_BRANCHING_PRECONDITIONS = 6;
    public const VERSION_LAST_ACTION = 5;
    public const VERSION_DURATIONS = 4;
    public const VERSION_MULTIPLE_SECTIONS = 3;
    public const VERSION_ATTEMPTING = 2;

    private int $version;
    private string $branch;

    /**
     * Writes version into binary storage.
     *
     * @param QtiBinaryStreamAccess $access
     * @throws BinaryStreamAccessException
     */
    public function persist(QtiBinaryStreamAccess $access): void
    {
        $access->writeTinyInt(self::CURRENT_VERSION);
        $access->writeString(self::CURRENT_BRANCH);
    }

    /**
     * Reads version from binary storage.
     *
     * @param QtiBinaryStreamAccess $access
     * @throws BinaryStreamAccessException
     */
    public function retrieve(QtiBinaryStreamAccess $access): void
    {
        $this->version = $access->readTinyInt();

        $this->branch = $this->isInBothBranches()
            ? $access->readString()
            : 'L';
    }

    /**
     * @return bool
     */
    public function isMaster(): bool
    {
        return $this->branch === 'M';
    }

    /**
     * @return bool
     */
    public function isLegacy(): bool
    {
        return $this->branch === 'L';
    }

    /**
     * @return bool
     */
    public function isCurrentVersion(): bool
    {
        return $this->version === self::CURRENT_VERSION;
    }

    /**
     * @return bool
     */
    public function storesRouteCountAsInteger(): bool
    {
        return $this->version >= self::VERSION_ROUTE_COUNT_INTEGER;
    }

    /**
     * @return bool
     */
    public function storesVariableDefaultValueInitializationFlag(): bool
    {
        return $this->version >= self::VERSION_VARIABLE_WITH_DEFAULT_VALUE_INITIALIZATION_FLAG;
    }

    /**
     * @return bool
     */
    public function storesVariableCountAsInteger(): bool
    {
        return $this->version >= self::VERSION_VARIABLE_COUNT_INTEGER;
    }

    /**
     * @return bool
     */
    public function isInBothBranches(): bool
    {
        return $this->version >= self::VERSION_FIRST_MASTER;
    }

    /**
     * @return bool
     */
    public function storesPositionAndRouteCountAsInteger(): bool
    {
        return $this->version >= self::VERSION_POSITION_INTEGER;
    }

    /**
     * @return bool
     */
    public function storesTrackPath(): bool
    {
        return $this->version >= self::VERSION_TRACK_PATH;
    }

    /**
     * @return bool
     */
    public function storesAlwaysAllowJumps(): bool
    {
        return $this->version >= self::VERSION_ALWAYS_ALLOW_JUMPS;
    }

    /**
     * @return bool
     */
    public function storesForceBranchingAndPreconditions(): bool
    {
        return $this->version >= self::VERSION_FORCE_BRANCHING_PRECONDITIONS;
    }

    /**
     * @return bool
     */
    public function storesLastAction(): bool
    {
        return $this->version >= self::VERSION_LAST_ACTION;
    }

    /**
     * @return bool
     */
    public function storesDurations(): bool
    {
        return $this->version >= self::VERSION_DURATIONS;
    }

    /**
     * @return bool
     */
    public function storesMultipleSections(): bool
    {
        return $this->version >= self::VERSION_MULTIPLE_SECTIONS;
    }

    /**
     * @return bool
     */
    public function storesAttempting(): bool
    {
        return $this->version >= self::VERSION_ATTEMPTING;
    }
}
