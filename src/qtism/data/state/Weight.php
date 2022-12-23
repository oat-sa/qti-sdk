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
use qtism\common\utils\Format;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiIdentifiable;
use qtism\data\QtiIdentifiableTrait;
use SplObjectStorage;

/**
 * From IMS QTI:
 *
 * The contribution of an individual item score to an overall test score typically
 * varies from test to test. The score of the item is said to be weighted. Weights
 * are defined as part of each reference to an item (assessmentItemRef) within a test.
 */
class Weight extends QtiComponent implements QtiIdentifiable
{
    use QtiIdentifiableTrait;

    /**
     * A QTI identifier.
     *
     * @var string
     * @qtism-bean-property
     */
    private $identifier;

    /**
     * A floating point value corresponding to the wheight to be applied on outcome
     * variables.
     *
     * @var int|float
     * @qtism-bean-property
     */
    private $value;

    /**
     * Create a new instance of Weight.
     *
     * @param string $identifier A QTI identifier.
     * @param int|float $value An integer/float value.
     * @throws InvalidArgumentException If $identifier is not a valid QTI identifier or if $value is not a float nor an integer.
     */
    public function __construct($identifier, $value)
    {
        $this->setObservers(new SplObjectStorage());

        $this->setIdentifier($identifier);
        $this->setValue($value);
    }

    /**
     * Get the identifier of the Weight.
     *
     * @return string A QTI identifier.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Set the identifier of the Weight.
     *
     * @param string $identifier A QTI Identifier.
     * @throws InvalidArgumentException If $identifier is not a valid QTI identifier.
     */
    public function setIdentifier($identifier): void
    {
        if (Format::isIdentifier($identifier, false)) {
            $this->identifier = $identifier;
            $this->notify();
        } else {
            $msg = "'${identifier}' is not a valid QTI Identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the value of the Weight.
     *
     * @return int|float An integer/float value.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of the Weight.
     *
     * @param int|float $value A in integer/float value.
     * @throws InvalidArgumentException If $value is not an integer nor a float.
     */
    public function setValue($value): void
    {
        if (is_int($value) || is_float($value)) {
            $this->value = $value;
        } else {
            $msg = "The value of a Weight must be a valid integer or float value, '" . gettype($value) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'weight';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        return new QtiComponentCollection();
    }

    public function __clone()
    {
        $this->setObservers(new SplObjectStorage());
    }
}
