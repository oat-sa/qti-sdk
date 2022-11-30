<?php

declare(strict_types=1);

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

use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * From IMS QTI:
 *
 * An abstract class associated with an outcomeDeclaration used to create a lookup table
 * from a numeric source value to a single outcome value in the declared value set. A
 * lookup table works in the reverse sense to the similar mapping as it defines how a
 * source numeric value is transformed into the outcome value, whereas a (response) mapping
 * defines how the response value is mapped onto a target numeric value.
 *
 * The transformation takes place using the lookupOutcomeValue rule within responseProcessing
 * or outcomeProcessing.
 */
abstract class LookupTable extends QtiComponent
{
    /**
     * The default outcome value to be used when no matching table entry is found. If omitted,
     * the NULL value is used. (QTI valueType attribute).
     *
     * @var mixed
     * @qtism-bean-property
     */
    private $defaultValue = null;

    /**
     * Create a new instance of LookupTable.
     *
     * @param mixed $defaultValue The default oucome value to be used when no matching table entry is found.
     */
    public function __construct($defaultValue = null)
    {
        $this->setDefaultValue($defaultValue);
    }

    /**
     * Get the default outcome value to be used when no matching table entry is found. If omitted,
     * the NULL value is returned.
     *
     * @return mixed A value.
     */
    #[\ReturnTypeWillChange]
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Get the default outcome value to be used when no matching table entry is found.
     *
     * @param mixed $defaultValue A value.
     */
    public function setDefaultValue($defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'lookupTable';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        return new QtiComponentCollection();
    }
}
