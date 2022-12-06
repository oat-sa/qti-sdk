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
 * The subtract operator takes 2 sub-expressions which all have single cardinality
 * and numerical base-types. The result is a single float or, if both sub-expressions
 * are of integer type, a single integer that corresponds to the first value minus
 * the second. If either of the sub-expressions is NULL then the operator results
 * in NULL.
 */
class Subtract extends Operator
{
    /**
     * Create a new Subtract object.
     *
     * @param ExpressionCollection $expressions
     */
    public function __construct(ExpressionCollection $expressions)
    {
        parent::__construct($expressions, 2, 2, [OperatorCardinality::SINGLE], [OperatorBaseType::INTEGER, OperatorBaseType::FLOAT]);
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'subtract';
    }
}
