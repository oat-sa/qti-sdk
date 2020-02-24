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

namespace qtism\runtime\common;

use InvalidArgumentException;
use qtism\common\collections\Container as baseContainer;
use qtism\data\state\ValueCollection;
use qtism\DeprecatedTrait;

/**
 * A generic Collection which is able to contain any QTI Scalar Datatype in
 * addition with the null value.
 *
 * From IMS QTI:
 *
 * A container is an aggregate data type that can contain multiple values
 * of the primitive Base-types. Containers may be empty.
 *
 * A container contains a list of values, this list may be empty in which
 * case it is treated as NULL. All the values in a multiple or ordered
 * container are drawn from the same value set, however, containers may
 * contain multiple occurrences of the same value. In other words, [A,B,B,C]
 * is an acceptable value for a container. A container with cardinality
 * multiple and value [A,B,C] is equivalent to a similar one with
 * value [C,B,A] whereas these two values would be considered distinct
 * for containers with cardinality ordered. When used as the value of
 * a response variable this distinction is typified by the difference
 * between selecting choices in a multi-response multi-choice task and
 * ranking choices in an order objects task. In the language of [ISO11404]
 * a container with multiple cardinality is a "bag-type", a container with
 * ordered cardinality is a "sequence-type" and a container with record
 * cardinality is a "record-type".
 *
 * The record container type is a special container that contains a set
 * of independent values each identified by its own identifier and
 * having its own base-type. This specification does not make use of
 * the record type directly however it is provided to enable
 * customInteractions to manipulate more complex responses and
 * customOperators to return more complex values, in addition
 * to the use for detailed information about numeric responses
 * described in the stringInteraction abstract class.
 *
 * @deprecated since 0.20.0. Use \qtism\common\collections\Container instead.
 */
class Container extends baseContainer
{
    use DeprecatedTrait;

    const DEPRECATED_SINCE = '0.20.0';
    const DEPRECATED_REPLACE_CLASS = baseContainer::class;

    /**
     * Create a new Container object.
     *
     * @param array $array An array of values to be set in the container.
     */
    public function __construct(array $array = [])
    {
        $this->deprecateClass();

        parent::__construct($array);
    }

    /**
     * Create a Container object from a Data Model ValueCollection object.
     *
     * @param ValueCollection $valueCollection A collection of qtism\data\state\Value objects.
     * @return BaseContainer A Container object populated with the values found in $valueCollection.
     * @throws InvalidArgumentException If a value from $valueCollection is not compliant with the QTI Runtime Model or the container type.
     */
    public static function createFromDataModel(ValueCollection $valueCollection)
    {
        self::deprecateClassStatic();

        return parent::createFromDataModel($valueCollection);
    }
}
