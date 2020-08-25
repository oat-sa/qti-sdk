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
use qtism\data\expressions\operators\Divide;

/**
 * The DivideProcessor class aims at processing Divide operators.
 *
 * From IMS QTI:
 *
 * The divide operator takes 2 sub-expressions which both have single cardinality and
 * numerical base-types. The result is a single float that corresponds to the first
 * expression divided by the second expression. If either of the sub-expressions is
 * NULL then the operator results in NULL.
 *
 * Item authors should make every effort to ensure that the value of the second
 * expression is never 0, however, if it is zero or the resulting value is outside
 * the value set defined by float (not including positive and negative infinity) then
 * the operator should result in NULL.
 */
class DivideProcessor extends OperatorProcessor
{
    /**
     * Process the Divide operator.
     *
     * @return QtiFloat|null A float value that corresponds to the first expression divided by the second or NULL if either of the sub-expressions is NULL or the result is outside the value set defined by float.
     * @throws OperatorProcessingException
     */
    public function process()
    {
        $operands = $this->getOperands();

        if ($operands->containsNull() === true) {
            return null;
        }

        if ($operands->exclusivelySingle() === false) {
            $msg = 'The Divide operator only accepts operands with a single cardinality.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
        }

        if ($operands->exclusivelyNumeric() === false) {
            $msg = 'The Divide operator only accepts operands with a baseType of integer or float.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
        }

        $operand1 = $operands[0];
        $operand2 = $operands[1];

        if ($operand2->getValue() == 0) {
            return null;
        }

        $divide = (float)($operand1->getValue() / $operand2->getValue());

        return is_nan($divide) ? null : new QtiFloat($divide);
    }

    /**
     * @return string
     */
    protected function getExpressionType()
    {
        return Divide::class;
    }
}
