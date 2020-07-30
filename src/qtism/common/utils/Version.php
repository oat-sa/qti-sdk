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
 * @license GPLv2
 */

namespace qtism\common\utils;

use InvalidArgumentException;

/**
 * This utility class provides utility classes about Semantic Versionning.
 *
 * @see http://semver.org Semantic Versioning
 */
class Version
{
    const SUPPORTED_VERSIONS = ['2.0.0', '2.1.0', '2.1.1', '2.2.0', '2.2.1', '2.2.2', '3.0.0'];

    const UNSUPPORTED_VERSION_MESSAGE = 'QTI version "%s" is not supported.';

    /**
     * Compare two version numbers of QTI, following the rules of Semantic Versioning.
     *
     * This method provides QTI version comparison. Two versions are compared using an optional operator.
     * When no $operator is provided, this method returns
     *
     * * -1 if $version1 is lower than $version2
     * * 0 if $version1 is equal to $version2
     * * 1 if $version1 is greater than $version2
     *
     * In case of using a specific $operator, the method returns true or false depending on
     * the $operator and versions given. Accepted operators are '<', 'lt', '<=', 'le', '>', 'gt',
     * '>=', 'ge', '==', '=', 'eq', '!=' and 'ne'.
     *
     * If an unknown QTI version is given for $version1 or $version2 arguments, or if $operator
     * is an unknown operator, an InvalidArgumentException is thrown.
     *
     * Important note: This metod will consider version '2.1' and '2.1.0' equal.
     *
     * @param string $version1 A version number.
     * @param string $version2 A version number
     * @param string $operator An operator.
     * @return mixed
     * @throws InvalidArgumentException
     * @see http://semver.org Semantic Versioning
     */
    public static function compare($version1, $version2, $operator = null)
    {
        $version1 = self::sanitize($version1);
        $version2 = self::sanitize($version2);
        self::checkOperator($operator);

        return $operator === null
            ? version_compare($version1, $version2)
            : version_compare($version1, $version2, $operator);
    }

    /**
     * Checks whether the given version is supported and adds
     * patch version if missing, i.e. '2.1' becomes '2.1.0'.
     *
     * @param string $version A version with major, minor and optional patch version e.g. '2.1' or '2.1.1'.
     * @return string Semantic version with optionally added patch (defaults to 0), e.g. '2.1' becomes '2.1.0'.
     * @throws InvalidArgumentException when version is not supported.
     */
    public static function sanitize(string $version): string
    {
        $patchedVersion = self::appendPatchVersion($version);
        if (!in_array($patchedVersion, static::SUPPORTED_VERSIONS, true)) {
            throw QtiVersionException::unsupportedVersion(static::UNSUPPORTED_VERSION_MESSAGE, $version, static::SUPPORTED_VERSIONS);
        }

        return $patchedVersion;
    }

    /**
     * Checks whether the operator is known.
     *
     * @param string $operator
     * @throws InvalidArgumentException when the operator is not known.
     */
    protected static function checkOperator($operator)
    {
        $knownOperators = ['<', 'lt', '<=', 'le', '>', 'gt', '>=', 'ge', '==', '=', 'eq', '!=', '<>', 'ne'];
        if ($operator !== null && !in_array($operator, $knownOperators, true)) {
            throw new InvalidArgumentException(
                sprintf("Unknown operator '%s'. Known operators are '%s'.",
                    $operator,
                    implode("', '", $knownOperators)
                )
            );
        }
    }

    /**
     * Append patch version to $version if $version only contains
     * major and minor versions.
     *
     * @param string $versionNumber
     * @return string
     * @throws InvalidArgumentException when the given version is not semantic.
     */
    public static function appendPatchVersion($versionNumber): string
    {
        $versionNumber = trim($versionNumber);

        if (preg_match('/^\d+\.\d+\.\d+$/', $versionNumber)) {
            return $versionNumber;
        }
        if (preg_match('/^\d+\.\d+$/', $versionNumber)) {
            return $versionNumber . '.0';
        }
        if (preg_match('/^\d+$/', $versionNumber)) {
            return $versionNumber . '.0.0';
        }

        throw new InvalidArgumentException(
            sprintf("Provided version number '%s' is not compliant to semantic versioning.", $versionNumber)
        );
    }
}
