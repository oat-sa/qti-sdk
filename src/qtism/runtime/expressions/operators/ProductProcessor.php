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
use qtism\common\datatypes\QtiScalar;
use qtism\data\expressions\operators\Product;

/**
 * The ProductProcessor class aims at processing Product QTI Data Model Operators.
 *
 * From IMS QTI:
 *
 * The product operator takes 1 or more sub-expressions which all have numerical
 * base-types and may have single, multiple or ordered cardinality. The result is
 * a single float or, if all sub-expressions are of integer type, a single integer
 * that corresponds to the product of the numerical values of the sub-expressions.
 * If any of the sub-expressions are NULL then the operator results in NULL.
 */
class ProductProcessor extends OperatorProcessor
{
    /**
     * Process the Product operator.
     *
     * @return QtiInteger|QtiFloat
     * @throws OperatorProcessingException If invalid operands are given.
     */
    public function process()
    {
        $operands = $this->getOperands();

        if ($operands->containsNull() === true) {
            return null;
        } elseif ($operands->anythingButRecord() === false) {
            $msg = 'The Product operator only accepts operands with a single, multiple or ordered cardinality.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
        } elseif ($operands->exclusivelyNumeric() === false) {
            $msg = 'The Product operator only accepts operands with integer or float baseType.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
        }

        $returnValue = 1;

        foreach ($this->getOperands() as $operand) {
            if ($operand instanceof QtiScalar) {
                $returnValue *= $operand->getValue();
            } else {
                foreach ($operand as $val) {
                    $returnValue *= $val->getValue();
                }
            }
        }

        return (is_int($returnValue)) ? new QtiInteger($returnValue) : new QtiFloat($returnValue);
    }

    /**
     * @see \qtism\runtime\expressions\ExpressionProcessor::getExpressionType()
     */
    protected function getExpressionType()
    {
        return Product::class;
    }
}
