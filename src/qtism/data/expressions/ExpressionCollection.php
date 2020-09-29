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

namespace qtism\data\expressions;

use InvalidArgumentException;
use qtism\data\QtiComponentCollection;

/**
 * A collection of Expression objects.
 */
class ExpressionCollection extends QtiComponentCollection
{
    /**
     * Check if a given $value is an instance of Expression.
     *
     * @param mixed $value
     * @throws InvalidArgumentException If the given $value is not an instance of Expression.
     */
    protected function checkType($value)
    {
        if (!$value instanceof Expression) {
            $msg = "ExpressionCollection only accepts to store Expression objects, '" . gettype($value) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Checks whether this collection of expression is pure.
     *
     * @return bool
     */
    public function isPure()
    {
        foreach ($this as $expr) {
            if (!$expr->isPure()) {
                return false;
            }
        }

        return true;
    }
}
