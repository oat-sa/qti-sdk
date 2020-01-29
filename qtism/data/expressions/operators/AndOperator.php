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

/**
 * Please note that this class represents the QTI 'and' class.
 * We cannot use the 'And' class name because it is a reserved word
 * in PHP.
 *
 * From IMS QTI:
 *
 * The and operator takes one or more sub-expressions each with a base-type of
 * boolean and single cardinality. The result is a single boolean which is
 * true if all sub-expressions are true and false if any of them are false.
 * If one or more sub-expressions are NULL and all others are true then the
 * operator also results in NULL.
 */
class AndOperator extends Operator
{
    /**
     * Create a new And object.
     *
     * @param ExpressionCollection $expressions
     */
    public function __construct(ExpressionCollection $expressions)
    {
        parent::__construct($expressions, 1, -1, [Cardinality::SINGLE], [OperatorBaseType::BOOLEAN]);
    }

    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName()
    {
        return 'and';
    }
}
