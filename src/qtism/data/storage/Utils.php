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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage;

use InvalidArgumentException;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\common\utils\Format;
use RuntimeException;
use UnexpectedValueException;

/**
 * XML Storage Utility class.
 */
class Utils
{
    /**
     * Transform a string representing a QTI valueType value in a
     * the correct datatype.
     *
     * @param string|null $string The QTI valueType value as a string.
     * @param int $baseType The QTI baseType that defines the datatype of $string.
     * @return mixed A converted object/primitive type.
     * @throws InvalidArgumentException If $baseType is not a value from the BaseType enumeration.
     * @throws UnexpectedValueException If $string cannot be transformed in a Value expression with the given $baseType.
     */
    public static function stringToDatatype(?string $string, int $baseType)
    {
        $string = $string ?? '';
        
        if (!in_array($baseType, BaseType::asArray(), true)) {
            $msg = 'BaseType must be a value from the BaseType enumeration.';
            throw new InvalidArgumentException($msg);
        }

        switch ($baseType) {
            case BaseType::BOOLEAN:
                if (!Format::isBoolean($string)) {
                    $msg = "'${string}' cannot be transformed into boolean.";
                    throw new UnexpectedValueException($msg);
                }

                return strtolower(trim($string)) === 'true';

            case BaseType::INTEGER:
                if (!Format::isInteger($string)) {
                    $msg = "'${string}' cannot be transformed into integer.";
                    throw new UnexpectedValueException($msg);
                }

                return (int)$string;

            case BaseType::FLOAT:
                if (!Format::isFloat($string)) {
                    $msg = "'${string}' cannot be transformed into float.";
                    throw new UnexpectedValueException($msg);
                }

                return (float)$string;

            case BaseType::URI:
                if (!Format::isUri($string)) {
                    $msg = "'${string}' is not a valid URI.";
                    throw new UnexpectedValueException($msg);
                }

                return $string;

            case BaseType::IDENTIFIER:
                if (!Format::isIdentifier($string)) {
                    $msg = "'${string}' is not a valid QTI Identifier.";
                    throw new UnexpectedValueException($msg);
                }

                return $string;

            case BaseType::INT_OR_IDENTIFIER:
                if (Format::isIdentifier($string)) {
                    return $string;
                }

                if (Format::isInteger($string)) {
                    return (int)$string;
                }

                $msg = "'${string}' is not a valid QTI Identifier nor a valid integer.";
                throw new UnexpectedValueException($msg);

            case BaseType::PAIR:
                if (!Format::isPair($string)) {
                    $msg = "'${string}' is not a valid pair.";
                    throw new UnexpectedValueException($msg);
                }

                $pair = explode("\x20", $string);
                return new QtiPair($pair[0], $pair[1]);

            case BaseType::DIRECTED_PAIR:
                if (!Format::isDirectedPair($string)) {
                    $msg = "'${string}' is not a valid directed pair.";
                    throw new UnexpectedValueException($msg);
                }

                $pair = explode("\x20", $string);
                return new QtiDirectedPair($pair[0], $pair[1]);

            case BaseType::DURATION:
                if (!Format::isDuration($string)) {
                    $msg = "'${string}' is not a valid duration.";
                    throw new UnexpectedValueException($msg);
                }

                return new QtiDuration($string);

            case BaseType::FILE:
                throw new RuntimeException('Unsupported baseType: file.');

            case BaseType::STRING:
                return '' . $string;

            case BaseType::POINT:
                if (!Format::isPoint($string)) {
                    $msg = "'${string}' is not valid point.";
                    throw new UnexpectedValueException($msg);
                }

                $parts = explode("\x20", $string);
                return new QtiPoint((int)$parts[0], (int)$parts[1]);
        }
    }

    /**
     * Transforms a string into a boolean.
     * Only strings that can be read as "true" or "false" are converted.
     *
     * @param string $string
     * @return bool
     * @throws UnexpectedValueException when the string cannot be converted to boolean.
     */
    public static function stringToBoolean(string $string): bool
    {
        if (!Format::isBoolean($string)) {
            throw new UnexpectedValueException("'${string}' cannot be transformed into boolean.");
        }

        return strtolower(trim($string)) === 'true';
    }

    /**
     * Transforms a string to a Coord object according to a given shape.
     *
     * @param string $string Coordinates as a string.
     * @param int $shape A value from the Shape enumeration.
     * @return QtiCoords A Coords object.
     * @throws UnexpectedValueException If $string cannot be converted to a Coords object.
     * @throws InvalidArgumentException If $string is are not valid coordinates or $shape is not a value from the Shape enumeration.
     */
    public static function stringToCoords(string $string, int $shape)
    {
        if (Format::isCoords($string)) {
            $stringCoords = explode(',', $string);
            $intCoords = [];

            foreach ($stringCoords as $sC) {
                $intCoords[] = (int)$sC;
            }

            // Maybe it was accepted has coords, but is it buildable with
            // the given shape?
            return new QtiCoords($shape, $intCoords);
        } else {
            throw new UnexpectedValueException("'${string}' cannot be converted to Coords.");
        }
    }

    /**
     * Sanitize a URI (Uniform Resource Identifier).
     *
     * The following processings will be applied:
     *
     * * If there is/are trailing slashe(s), they will be removed.
     *
     * @param string $uri A Uniform Resource Identifier.
     * @return string A sanitized Uniform Resource Identifier.
     * @throws InvalidArgumentException If $uri is not a string.
     */
    public static function sanitizeUri($uri)
    {
        if (is_string($uri)) {
            return rtrim($uri, '/');
        }

        $msg = "The uri argument must be a string, '" . gettype($uri) . "' given.";
        throw new InvalidArgumentException($msg);
    }
}
