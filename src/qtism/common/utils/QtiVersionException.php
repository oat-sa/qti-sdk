<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\utils;

use InvalidArgumentException;

/**
 * An exception type that represent an error when dealing with QTI-XML files.
 */
class QtiVersionException extends InvalidArgumentException
{
    const SUPPORTED_COMPACT_VERSIONS = ['2.1.0', '2.1.1', '2.2.0', '2.2.1', '2.2.2'];
    const SUPPORTED_RESULT_VERSIONS = ['2.1.0', '2.1.1', '2.2.0', '2.2.1', '2.2.2'];

    public static function unsupportedQtiVersion(string $versionNumber): self
    {
        return self::unsupportedVersion(
            'QTI version "%s" is not supported.',
            $versionNumber,
            Version::SUPPORTED_QTI_VERSIONS
        );
    }

    public static function unsupportedCompactVersion(string $versionNumber): self
    {
        return self::unsupportedVersion(
            'QTI Compact is not supported for version "%s".',
            $versionNumber,
            self::SUPPORTED_COMPACT_VERSIONS
        );
    }

    public static function unsupportedResultVersion(string $versionNumber): self
    {
        return self::unsupportedVersion(
            'QTI Result Report is not supported for version "%s".',
            $versionNumber,
            self::SUPPORTED_RESULT_VERSIONS
        );
    }

    public static function unsupportedVersion(string $message, string $versionNumber, array $supportedVersions): self
    {
        return new self(
            sprintf(
                $message . ' Supported versions are "%s".',
                $versionNumber,
                implode('", "', $supportedVersions)
            )
        );
    }
}
