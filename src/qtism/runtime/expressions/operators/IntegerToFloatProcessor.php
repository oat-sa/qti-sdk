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

use qtism\common\datatypes\QtiFloat;
use qtism\data\expressions\operators\IntegerToFloat;

/**
 * The IntegerToFloatProcessor class aims at processing IntegerToFloat operators.
 *
 * From IMS QTI:
 *
 * The integer to float conversion operator takes a single sub-expression which must
 * have single cardinality and base-type integer. The result is a value of base type
 * float with the same numeric value. If the sub-expression is NULL then the operator
 * results in NULL.
 */
class IntegerToFloatProcessor extends OperatorProcessor
{
    /**
     * Process the IntegerToFloat operator.
     *
     * @return QtiFloat|null A float value with the same numeric value as the sub-expression or NULL if the sub-expression is considered to be NULL.
     * @throws OperatorProcessingException
     */
    public function process()
    {
        $operands = $this->getOperands();

        if ($operands->containsNull() === true) {
            return null;
        }

        if ($operands->exclusivelySingle() === false) {
            $msg = 'The IntegerToFloat operator only accepts operands with a single cardinality.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
        }

        if ($operands->exclusivelyInteger() === false) {
            $msg = 'The IntegerToFloat operator only accepts operands with baseType integer.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
        }

        $operand = $operands[0];

        return new QtiFloat((float)$operand->getValue());
    }

    /**
     * @return string
     */
    protected function getExpressionType()
    {
        return IntegerToFloat::class;
    }
}
