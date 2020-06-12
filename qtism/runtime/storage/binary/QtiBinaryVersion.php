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

class QtiBinaryVersion
{
    /**
     * The QTI binary data version number.
     *
     * @var int
     */
    const CURRENT_VERSION = 10;

    /**
     * The QTI Sdk branch to select behaviour of the binary storage.
     * 'M' denotes Master. 'L' denotes Legacy.
     *
     * @var string
     */
    const CURRENT_BRANCH = 'L';

    /**
     * These constants make the different versions a bit more self explanatory.
     */
    const VERSION_FIRST_MASTER = 10;

    const VERSION_POSITION_INTEGER = 9;

    const VERSION_ALWAYS_ALLOW_JUMPS = 8;

    const VERSION_TRACK_PATH = 7;

    const VERSION_FORCE_BRANCHING_PRECONDITIONS = 6;

    const VERSION_LAST_ACTION = 5;

    const VERSION_DURATIONS = 4;

    const VERSION_MULTIPLE_SECTIONS = 3;

    const VERSION_ATTEMPTING = 2;

    /**
     * @var int
     */
    private $version;

    /**
     * @var string
     */
    private $branch;

    /**
     * Writes version into binary storage.
     *
     * @param QtiBinaryStreamAccess $access
     * @throws BinaryStreamAccessException
     */
    public function persist(QtiBinaryStreamAccess $access)
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
    public function retrieve(QtiBinaryStreamAccess $access)
    {
        $this->version = $access->readTinyInt();

        $this->branch = $this->isInBothBranches()
            ? $access->readString()
            : 'L';
    }

    public function isMaster(): bool
    {
        return $this->branch === 'M';
    }

    public function isLegacy(): bool
    {
        return $this->branch === 'L';
    }

    public function isCurrentVersion(): bool
    {
        return $this->version = self::CURRENT_VERSION;
    }

    public function isInBothBranches(): bool
    {
        return $this->version >= self::VERSION_FIRST_MASTER;
    }

    public function storesPositionAndRouteCountAsInteger(): bool
    {
        return $this->version >= self::VERSION_POSITION_INTEGER;
    }

    public function storesTrackPath(): bool
    {
        return $this->version >= self::VERSION_TRACK_PATH;
    }

    public function storesAlwaysAllowJumps(): bool
    {
        return $this->version >= self::VERSION_ALWAYS_ALLOW_JUMPS;
    }

    public function storesForceBranchingAndPreconditions(): bool
    {
        return $this->version >= self::VERSION_FORCE_BRANCHING_PRECONDITIONS;
    }

    public function storesLastAction(): bool
    {
        return $this->version >= self::VERSION_LAST_ACTION;
    }

    public function storesDurations(): bool
    {
        return $this->version >= self::VERSION_DURATIONS;
    }

    public function storesMultipleSections(): bool
    {
        return $this->version >= self::VERSION_MULTIPLE_SECTIONS;
    }

    public function storesAttempting(): bool
    {
        return $this->version >= self::VERSION_ATTEMPTING;
    }
}
