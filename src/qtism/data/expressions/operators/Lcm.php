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

namespace qtism\data\expressions\operators;

use qtism\common\enums\Cardinality;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\Pure;

/**
 * From IMS QTI:
 *
 * The lcm operator takes 1 or more sub-expressions which all have base-type
 * integer and may have single, multiple or ordered cardinality. The result
 * is a single integer equal in value to the lowest common multiple (lcm) of
 * the argument values. If any argument is zero, the result is 0, lcm(0,n)=0;
 * authors should beware of this in calculations which require division by the
 * lcm of random values. If any of the sub-expressions is NULL, the result is NULL.
 * If any of the sub-expressions is not a numerical value, then the result is NULL.
 */
class Lcm extends Operator implements Pure
{
    /**
     * Create a new Lcm object.
     *
     * @param ExpressionCollection $expressions A collection of Expression objects.
     */
    public function __construct(ExpressionCollection $expressions)
    {
        parent::__construct($expressions, 1, -1, [Cardinality::SINGLE, Cardinality::MULTIPLE, Cardinality::ORDERED], [OperatorBaseType::INTEGER]);
    }

    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName()
    {
        return 'lcm';
    }

    /**
     * Checks whether this expression is pure.
     *
     * @link https://en.wikipedia.org/wiki/Pure_function
     *
     * @return bool True if the expression is pure, false otherwise
     */
    public function isPure()
    {
        return $this->getExpressions()->isPure();
    }
}
