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
use qtism\common\datatypes\QtiInteger;
use qtism\data\expressions\operators\Truncate;

/**
 * The TruncateProcessor class aims at processing Truncate expressions.
 *
 * From IMS QTI:
 *
 * The truncate operator takes a single sub-expression which must have single
 * cardinality and a numerical base-type. The result is a value of base-type
 * integer formed by truncating the value of the sub-expression towards zero.
 * For example, the value 6.8 becomes 6 and the value -6.8 becomes -6. If
 * the sub-expression is NULL then the operator results in NULL. If the
 * sub-expression is NaN, then the result is NULL. If the sub-expression is
 * INF, then the result is INF. If the sub-expression is -INF, then the
 * result is -INF.
 */
class TruncateProcessor extends OperatorProcessor
{
    /**
     * Process the Truncate operator.
     *
     * @return QtiInteger|QtiFloat|null The truncated value or NULL if the sub-expression is NaN or if the sub-expression is NULL.
     * @throws OperatorProcessingException
     */
    #[\ReturnTypeWillChange]
    public function process()
    {
        $operands = $this->getOperands();

        if ($operands->containsNull() === true) {
            return null;
        }

        if ($operands->exclusivelySingle() === false) {
            $msg = 'The Truncate operator only accepts operands with a single cardinality.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
        }

        if ($operands->exclusivelyNumeric() === false) {
            $msg = 'The Truncate operator only accepts operands with an integer or float baseType.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
        }

        $operand = $operands[0];

        if (is_nan($operand->getValue())) {
            return null;
        } elseif (is_infinite($operand->getValue())) {
            return new QtiFloat(INF);
        } else {
            return new QtiInteger((int)$operand->getValue());
        }
    }

    /**
     * @return string
     */
    protected function getExpressionType(): string
    {
        return Truncate::class;
    }
}
