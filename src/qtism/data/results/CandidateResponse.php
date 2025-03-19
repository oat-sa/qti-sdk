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
 * Copyright (c) 2018-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille <camille@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\results;

use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\state\ValueCollection;

/**
 * Class CandidateResponse
 *
 * The response given by the candidate.
 */
class CandidateResponse extends QtiComponent
{
    /**
     * The value(s) of the response variable. A NULL value, resulting from no response, is indicated by the absence of any value.
     * The order of the values is significant only if the response was declared with ordered cardinality.
     *
     * Multiplicity [0,*]
     *
     * @var ValueCollection
     */
    protected $values;

    /**
     * CandidateResponse constructor.
     *
     * @param ValueCollection|null $values
     */
    public function __construct(?ValueCollection $values = null)
    {
        $this->setValues($values);
    }

    /**
     * Returns the QTI class name as per QTI 2.1 specification.
     *
     * @return string A QTI class name.
     */
    public function getQtiClassName(): string
    {
        return 'candidateResponse';
    }

    /**
     * Get the direct child components of this one.
     *
     * @return QtiComponentCollection A collection of QtiComponent objects.
     */
    public function getComponents(): QtiComponentCollection
    {
        $components = [];
        if ($this->hasValues()) {
            $components = $this->getValues()->getArrayCopy();
        }
        return new QtiComponentCollection($components);
    }

    /**
     * Get candidate response values.
     *
     * @return ValueCollection|null
     */
    public function getValues(): ?ValueCollection
    {
        return $this->values;
    }

    /**
     * Set candidate response values
     *
     * @param ValueCollection|null $values
     * @return $this
     */
    public function setValues(?ValueCollection $values = null)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * Check if the candidate response has values
     *
     * @return bool
     */
    public function hasValues(): bool
    {
        return $this->values !== null;
    }
}
