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
use qtism\common\enums\BaseType;
use qtism\data\expressions\operators\Min;
use qtism\runtime\common\Container;
use qtism\runtime\common\MultipleContainer;

/**
 * The MinProcessor class aims at processing Min QTI Data Model Expression
 * objects.
 *
 * From IMS QTI:
 *
 * The min operator takes 1 or more sub-expressions which all have numerical
 * base-types and may have single, multiple or ordered cardinality. The result
 * is a single float, or, if all sub-expressions are of integer type, a single
 * integer, equal in value to the smallest of the argument values, i.e. the
 * result is the argument closest to negative infinity. If the arguments have
 * the same value, the result is that same value. If any of the sub-expressions
 * is NULL, the result is NULL. If any of the sub-expressions is not a numerical
 * value, then the result is NULL.
 */
class MinProcessor extends OperatorProcessor
{
    /**
     * Process the current expression.
     *
     * @return QtiFloat|QtiInteger|null The smallest of the operand values or NULL if any of the operand values is NULL.
     * @throws OperatorProcessingException
     */
    public function process()
    {
        $operands = $this->getOperands();

        if ($operands->containsNull() === true) {
            return null;
        }

        if ($operands->anythingButRecord() === false) {
            $msg = 'The Min operator only accept values with a cardinality of single, multiple or ordered.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
        }

        if ($operands->exclusivelyNumeric() === false) {
            // As per QTI 2.1 spec, If any of the sub-expressions is not a numerical value, then the result is NULL.
            return null;
        }

        // As per QTI 2.1 spec,
        // The result is a single float, or, if all sub-expressions are of
        // integer type, a single integer.
        $integerCount = 0;
        $valueCount = 0;
        $min = PHP_INT_MAX;
        foreach ($operands as $operand) {
            if (!$operand instanceof Container) {
                $baseType = ($operand instanceof QtiFloat) ? BaseType::FLOAT : BaseType::INTEGER;
                $value = new MultipleContainer($baseType, [$operand]);
            } else {
                $value = $operand;
            }

            foreach ($value as $v) {
                if ($v === null) {
                    return null;
                }

                $valueCount++;
                $integerCount += ($v instanceof QtiInteger) ? 1 : 0;

                if ($v->getValue() < $min) {
                    $min = $v->getValue();
                }
            }
        }

        return ($integerCount === $valueCount) ? new QtiInteger((int)$min) : new QtiFloat((float)$min);
    }

    /**
     * @return string
     */
    protected function getExpressionType()
    {
        return Min::class;
    }
}
