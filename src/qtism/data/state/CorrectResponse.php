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
 * The CorrectResponse class.
 */
class CorrectResponse extends QtiComponent
{
    /**
     * From IMS QTI:
     *
     * A human readable interpretation of the correct value.
     *
     * @var string
     * @qtism-bean-property
     */
    private $interpretation = '';

    /**
     * From IMS QTI:
     *
     * The order of the values is signficant only when the response
     * is of ordered cardinality.
     *
     * @var ValueCollection
     * @qtism-bean-property
     */
    private $values;

    /**
     * Create a new instance of CorrectResponse.
     *
     * @param ValueCollection $values A collection of Value objects with at least one Value object.
     * @param string $interpretation A human-readable interpretation of the correct response.
     * @throws InvalidArgumentException If $values does not contain at least one Value object or $interpretation is not a string.
     */
    public function __construct(ValueCollection $values, $interpretation = '')
    {
        $this->setValues($values);
        $this->setInterpretation($interpretation);
    }

    /**
     * Get a human-readable interpretation of the correct response.
     * Returns an empty string if not specified.
     *
     * @return string An interpretation.
     */
    public function getInterpretation(): string
    {
        return $this->interpretation;
    }

    /**
     * Set a human-readable interpretation of the correct response.
     * Set an empty string if not specified.
     *
     * @param string $interpretation An interpretation.
     * @throws InvalidArgumentException If $interpretation is not a string.
     */
    public function setInterpretation($interpretation): void
    {
        if (is_string($interpretation)) {
            $this->interpretation = $interpretation;
        } else {
            $msg = "Interpretation must be a string, '" . gettype($interpretation) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the intrinsic values of the CorrectResponse.
     *
     * @return ValueCollection A ValueCollection containing at least one Value object.
     */
    public function getValues(): ValueCollection
    {
        return $this->values;
    }

    /**
     * Set the intrinsic values of the CorrectResponse.
     *
     * @param ValueCollection $values A collection of Value objects containing at least one Value object.
     * @throws InvalidArgumentException If $values does not contain at least one Value object.
     */
    public function setValues(ValueCollection $values): void
    {
        if (count($values) > 0) {
            $this->values = $values;
        } else {
            $msg = 'Values must contain at lease one Value.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'correctResponse';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        return new QtiComponentCollection($this->getValues()->getArrayCopy());
    }
}
