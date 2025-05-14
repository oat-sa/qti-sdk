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

namespace qtism\data\state;

use InvalidArgumentException;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * The QTI MapEntry class implementation.
 *
 * Author note: The specification says that the caseSensitive attribute is mandatory.
 * However, the XSD file for version 2.1 states that it is not. What is its default
 * value then? We decided to set it up to true.
 */
class MapEntry extends QtiComponent
{
    /**
     * From IMS QTI:
     *
     * The source value.
     *
     * Note: qti:valueType
     *
     * @var mixed
     * @qtism-bean-property
     */
    private $mapKey;

    /**
     * From IMS QTI:
     *
     * The mapped value.
     *
     * @var float
     * @qtism-bean-property
     */
    private $mappedValue;

    /**
     * From IMS QTI:
     *
     * Used to control whether a mapEntry string is matched case sensitively.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $caseSensitive = true;

    /**
     * Create a new MapEntry object.
     *
     * @param mixed $mapKey A qti:valueType value (any baseType).
     * @param float $mappedValue A mapped value.
     * @param bool $caseSensitive Whether a mapEntry string is matched case sensitively.
     * @throws InvalidArgumentException If $mappedValue is not a float or $caseSensitive is not a boolean.
     */
    public function __construct($mapKey, $mappedValue, $caseSensitive = true)
    {
        $this->setMapKey($mapKey);
        $this->setMappedValue($mappedValue);
        $this->setCaseSensitive($caseSensitive);
    }

    /**
     * Set the source value.
     *
     * @param mixed $mapKey A qti:valueType value.
     */
    public function setMapKey($mapKey): void
    {
        $this->mapKey = $mapKey;
    }

    /**
     * Get the source value.
     *
     * @return mixed A qti:valueType value.
     */
    #[\ReturnTypeWillChange]
    public function getMapKey()
    {
        return $this->mapKey;
    }

    /**
     * Set the mapped value.
     *
     * @param float $mappedValue A mapped value.
     * @throws InvalidArgumentException If $mappedValue is not a float value.
     */
    public function setMappedValue($mappedValue): void
    {
        if (is_float($mappedValue)) {
            $this->mappedValue = $mappedValue;
        } else {
            $msg = "The attribute 'mappedValue' must be a float value, '" . gettype($mappedValue) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the mapped value.
     *
     * @return float A mapped value.
     */
    public function getMappedValue(): float
    {
        return $this->mappedValue;
    }

    /**
     * Set whether the mapEntry string is matched case sensitively.
     *
     * @param bool $caseSensitive
     * @throws InvalidArgumentException If $caseSensitive is not a boolean value.
     */
    public function setCaseSensitive($caseSensitive): void
    {
        if (is_bool($caseSensitive)) {
            $this->caseSensitive = $caseSensitive;
        } else {
            $msg = "The attribute 'caseSensitive' must be a boolean value, '" . gettype($caseSensitive) . "'.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Whether the mapEntry string is matched case sensitively.
     *
     * @return bool
     */
    public function isCaseSensitive(): bool
    {
        return $this->caseSensitive;
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'mapEntry';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        return new QtiComponentCollection();
    }
}
