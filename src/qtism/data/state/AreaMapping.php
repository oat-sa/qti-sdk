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
 * From IMS QTI:
 *
 * A special class used to create a mapping from a source set of point values
 * to a target set of float values. When mapping containers, the result is
 * the sum of the mapped values from the target set. See mapResponsePoint
 * for details. The attributes have the same meaning as the similarly named
 * attributes on mapping.
 */
class AreaMapping extends QtiComponent
{
    /**
     * The lower bound.
     *
     * @var bool|float
     * @qtism-bean-property
     */
    private $lowerBound = false;

    /**
     * The upper bound.
     *
     * @var bool|float
     * @qtism-bean-property
     */
    private $upperBound = false;

    /**
     * The default value.
     *
     * @var float
     * @qtism-bean-property
     */
    private $defaultValue = 0.0;

    /**
     * A collection of AreaMapEntry objects.
     *
     * @var AreaMapEntryCollection
     * @qtism-bean-property
     */
    private $areaMapEntries;

    /**
     * Create a new AreaMapping object.
     *
     * @param AreaMapEntryCollection $areaMapEntries A collection of AreaMapEntry objects.
     * @param float $defaultValue A default value. Default is 0.
     * @param bool|float $lowerBound A lower bound. Give false if no lower bound.
     * @param bool|float $upperBound An upper bound. Give false if no upper bound.
     * @throws InvalidArgumentException If $lowerBound, $upperBound, $defaultValue are not float values or if $areaMapEntries is empty.
     */
    public function __construct(AreaMapEntryCollection $areaMapEntries, $defaultValue = 0.0, $lowerBound = false, $upperBound = false)
    {
        $this->setLowerBound($lowerBound);
        $this->setUpperBound($upperBound);
        $this->setDefaultValue($defaultValue);
        $this->setAreaMapEntries($areaMapEntries);
    }

    /**
     * Set the lower bound.
     *
     * @param bool|float $lowerBound A lower bound.
     * @throws InvalidArgumentException If $lowerBound is not a float value nor false.
     */
    public function setLowerBound($lowerBound): void
    {
        if (is_float($lowerBound) || (is_bool($lowerBound) && $lowerBound === false)) {
            $this->lowerBound = $lowerBound;
        } else {
            $msg = "The lowerBound argument must be a float or false if no lower bound, '" . gettype($lowerBound) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the lower bound.
     *
     * @return float|false A lower bound.
     */
    #[\ReturnTypeWillChange]
    public function getLowerBound()
    {
        return $this->lowerBound;
    }

    /**
     * Set the upper bound.
     *
     * @param bool|float $upperBound An upper bound.
     * @throws InvalidArgumentException If $upperBound is not a float value nor false.
     */
    public function setUpperBound($upperBound): void
    {
        if (is_float($upperBound) || (is_bool($upperBound) && $upperBound === false)) {
            $this->upperBound = $upperBound;
        } else {
            $msg = "The upperBound argument must be a float or false if no upper bound, '" . gettype($upperBound) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the upper bound.
     *
     * @return float|false An upper bound.
     */
    #[\ReturnTypeWillChange]
    public function getUpperBound()
    {
        return $this->upperBound;
    }

    /**
     * Set the default value.
     *
     * @param float $defaultValue A default value.
     * @throws InvalidArgumentException If $defaultValue is not a float value.
     */
    public function setDefaultValue($defaultValue): void
    {
        if (is_float($defaultValue)) {
            $this->defaultValue = $defaultValue;
        } else {
            $msg = "The defaultValue argument must be a numeric value, '" . gettype($defaultValue) . "'.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the default value.
     *
     * @return float A default value.
     */
    public function getDefaultValue(): float
    {
        return $this->defaultValue;
    }

    /**
     * Set the collection of AreaMapEntry objects composing the AreaMapping.
     *
     * @param AreaMapEntryCollection $areaMapEntries A collection of AreaMapEntry objects.
     */
    public function setAreaMapEntries(AreaMapEntryCollection $areaMapEntries): void
    {
        if (count($areaMapEntries) >= 1) {
            $this->areaMapEntries = $areaMapEntries;
        } else {
            $msg = 'An AreaMapping object must contain at least one AreaMapEntry object. none given.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the collection of AreaMapEntry objects composing the AreaMapping.
     *
     * @return AreaMapEntryCollection A collection of AreaMapEntry objects.
     */
    public function getAreaMapEntries(): AreaMapEntryCollection
    {
        return $this->areaMapEntries;
    }

    /**
     * Whether the AreaMapping has a lower bound.
     *
     * @return bool
     */
    public function hasLowerBound(): bool
    {
        return $this->getLowerBound() !== false;
    }

    /**
     * Whether the AreaMapping has an upper bound.
     *
     * @return bool
     */
    public function hasUpperBound(): bool
    {
        return $this->getUpperBound() !== false;
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'areaMapping';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        $comp = $this->getAreaMapEntries()->getArrayCopy();

        return new QtiComponentCollection($comp);
    }
}
