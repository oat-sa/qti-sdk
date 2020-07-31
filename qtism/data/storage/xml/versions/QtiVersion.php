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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\versions;

use InvalidArgumentException;
use qtism\common\utils\Version;

/**
 * Generic QTI version.
 */
class QtiVersion extends Version
{
    const SUPPORTED_VERSIONS = [
        '2.0.0',
        '2.1.0',
        '2.1.1',
        '2.2.0',
        '2.2.1',
        '2.2.2',
        '3.0.0',
    ];

    const UNSUPPORTED_VERSION_MESSAGE = 'QTI version "%s" is not supported.';

    /** @var string */
    private $versionNumber;

    public function __construct(string $versionNumber)
    {
        $this->versionNumber = $versionNumber;
    }

    public function __toString(): string
    {
        return $this->versionNumber;
    }

    /**
     * Creates a new Version given the version number.
     *
     * @param string $versionNumber
     * @return $this
     */
    public static function create(string $versionNumber): self
    {
        $versionNumber = self::sanitize($versionNumber);
        return new self($versionNumber);
    }

    /**
     * Checks that the given version is supported.
     *
     * @param string $version a semantic version
     * @throws InvalidArgumentException when the version is not supported.
     */
    protected static function checkVersion(string $version)
    {
        if (!in_array($version, static::SUPPORTED_VERSIONS, true)) {
            throw QtiVersionException::unsupportedVersion(static::UNSUPPORTED_VERSION_MESSAGE, $version, static::SUPPORTED_VERSIONS);
        }
    }
}
