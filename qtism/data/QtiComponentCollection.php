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

namespace qtism\data;

use InvalidArgumentException;
use qtism\common\collections\AbstractCollection;
use RuntimeException;

/**
 * A collection that aims at storing QtiComponent objects. The QtiComponentCollection
 * class must be used as a bag. Thus, no specific key must be set when setting a value
 * in the collection. If a specific key is provided, a RuntimeException will be thrown.
 */
class QtiComponentCollection extends AbstractCollection
{
    /**
     * Check if $value is a QtiComponent object.
     *
     * @param mixed $value The value of which we want to test the type.
     *
     * @throws InvalidArgumentException If $value is not a QtiComponent object.
     */
    protected function checkType($value)
    {
        if (!$value instanceof QtiComponent) {
            $msg = "QtiComponentCollection class only accept QtiComponent objects, '" . get_class($value) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (empty($offset)) {
            parent::offsetSet($offset, $value);
        } else {
            $msg = "QtiComponentCollection must be used as a bag (specific key '${offset}' given).";
            throw new RuntimeException($msg);
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if (empty($offset)) {
            parent::offsetUnset($offset);
        } else {
            $msg = "QtiComponentCollection must be used as a bag (specific key '${offset}' given).";
            throw new RuntimeException($msg);
        }
    }
}
