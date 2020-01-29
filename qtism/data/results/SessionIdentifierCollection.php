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

use InvalidArgumentException;
use qtism\data\QtiComponentCollection;

class SessionIdentifierCollection extends QtiComponentCollection
{
    /**
     * Check if a given $value is an instance of SessionIdentifier.
     *
     * @param mixed $value The value of which we want to test the type.
     * @throws InvalidArgumentException If the given $value is not an instance of SessionIdentifier.
     */
    protected function checkType($value)
    {
        if (!$value instanceof SessionIdentifier) {
            throw new InvalidArgumentException(sprintf(
                "SessionIdentifierCollection only accepts to store SessionIdentifier objects, '%s' given.",
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }
    }
}
