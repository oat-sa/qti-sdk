<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\data\expressions\operators\NotOperator;

/**
 * The NotProcessor class aims at processing Not QTI DataModel expressions.
 *
 * From IMS QTI:
 *
 * The not operator takes a single sub-expression with a base-type of boolean and single
 * cardinality. The result is a single boolean with a value obtained by the logical
 * negation of the sub-expression's value. If the sub-expression is NULL then the not
 * operator also results in NULL.
 */
class NotProcessor extends OperatorProcessor
{
    /**
     * Returns the logical negation of the sub-expressions.
     *
     * @return QtiBoolean
     * @throws OperatorProcessingException
     */
    public function process()
    {
        $operands = $this->getOperands();

        if ($operands->containsNull()) {
            return null;
        }

        if ($operands->exclusivelySingle() === false) {
            $msg = 'The Not Expression only accept operands with single cardinality.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
        }

        if ($operands->exclusivelyBoolean() === false) {
            $msg = 'The Not Expression only accept operands with boolean baseType.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
        }

        $operand = $operands[0];

        return new QtiBoolean(!$operand->getValue());
    }

    /**
     * @see \qtism\runtime\expressions\ExpressionProcessor::getExpressionType()
     */
    protected function getExpressionType()
    {
        return NotOperator::class;
    }
}
