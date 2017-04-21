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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\Pure;

/**
 * From IMS QTI:
 *
 * The not operator takes a single sub-expression with a base-type of boolean
 * and single cardinality. The result is a single boolean with a value obtained
 * by the logical negation of the sub-expression's value. If the sub-expression
 * is NULL then the not operator also results in NULL.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Not extends Operator implements Pure
{
    /**
     * Create a new Not object.
     *
     * @param \qtism\data\expressions\ExpressionCollection $expressions
     */
    public function __construct(ExpressionCollection $expressions)
    {
        parent::__construct($expressions, 1, 1, array(OperatorCardinality::SINGLE), array(OperatorBaseType::BOOLEAN));
    }

    /**
	 * @see \qtism\data\QtiComponent::getQtiClassName()
	 */
    public function getQtiClassName()
    {
        return 'not';
    }

    /**
     * Checks whether this expression is pure.
     * @link https://en.wikipedia.org/wiki/Pure_function
     *
     * @return boolean True if the expression is pure, false otherwise
     */
    public function isPure()
    {
        return $this->getExpressions()->isPure();
    }
}
