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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\datatypes;

use qtism\common\Comparable;

/**
 * A simple interface aiming at implementatin QTI datatypes. The following
 * QTI datatypes are implemented.
 *
 * * Boolean
 * * Coords
 * * DirectedPair
 * * Duration
 * * File
 * * Float
 * * Identifier
 * * Integer
 * * IntOrIdentifier
 * * Pair
 * * Point
 * * String
 * * Uri
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 */
interface QtiDatatype extends Comparable
{
    /**
     * Get the QTI baseType of the datatype instance.
     *
     * @return integer A value from the BaseType enumeration.
     */
    public function getBaseType();

    /**
     * Get the QTI cardinality of the datatype instance.
     *
     * @return integer A value from the Cardinality enumeration.
     */
    public function getCardinality();
}
