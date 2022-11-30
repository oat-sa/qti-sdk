<?php

declare(strict_types=1);

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
 * The lte operator takes two sub-expressions which must both have single cardinality
 * and have a numerical base-type. The result is a single boolean with a value of
 * true if the first expression is numerically less than or equal to the second and
 * false if it is greater than the second. If either sub-expression is NULL then
 * the operator results in NULL.
 */
class Lte extends Operator
{
    /**
     * Create a new Lte object.
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
        return 'lte';
    }
}
