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

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 *
 * The integer modulus operator takes 2 sub-expressions which both have single
 * cardinality and base-type integer. The result is the single integer that
 * corresponds to the remainder when the first expression (x) is divided by
 * the second expression (y). If z is the result of the corresponding
 * integerDivide operator then the result is x-z*y. If y is 0, or if either
 * of the sub-expressions is NULL then the operator results in NULL.
 */
class IntegerModulus extends Operator
{
    /**
     * Create a new IntegerModulus object.
     *
     * @param ExpressionCollection $expressions
     */
    public function __construct(ExpressionCollection $expressions)
    {
        parent::__construct($expressions, 2, 2, [OperatorCardinality::SINGLE], [OperatorBaseType::INTEGER]);
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'integerModulus';
    }
}
