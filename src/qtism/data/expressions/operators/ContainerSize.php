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

use qtism\common\enums\Cardinality;
use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 *
 * The containerSize operator takes a sub-expression with any base-type and either
 * multiple or ordered cardinality. The result is an integer giving the number of
 * values in the sub-expression, in other words, the size of the container. If
 * the sub-expression is NULL the result is 0. This operator can be used for
 * determining how many choices were selected in a multiple-response
 * choiceInteraction, for example.
 */
class ContainerSize extends Operator
{
    /**
     * Create a new ContainerSize object.
     *
     * @param ExpressionCollection $expressions
     */
    public function __construct(ExpressionCollection $expressions)
    {
        parent::__construct($expressions, 1, 1, [Cardinality::MULTIPLE, Cardinality::ORDERED], [OperatorBaseType::ANY]);
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'containerSize';
    }
}
