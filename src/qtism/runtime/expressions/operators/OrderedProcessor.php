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

use qtism\common\datatypes\QtiScalar;
use qtism\data\expressions\operators\Ordered;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\Utils as CommonUtils;

/**
 * The OrderedProcessor class aims at processing Ordered QTI Data Model Expression objects.
 *
 * From IMS QTI:
 *
 * The ordered operator takes 0 or more sub-expressions all of which must have
 * either single or ordered cardinality. Although the sub-expressions may be of
 * any base-type they must all be of the same base-type. The result is a
 * container with ordered cardinality containing the values of the
 * sub-expressions, sub-expressions with ordered cardinality have their
 * individual values added (in order) to the result: contains cannot
 * contain other containers. For example, when applied to A, B, {C,D}
 * the ordered operator results in {A,B,C,D}. Note that the ordered
 * operator never results in an empty container. All sub-expressions
 * with NULL values are ignored. If no sub-expressions are given
 * (or all are NULL) then the result is NULL
 */
class OrderedProcessor extends OperatorProcessor
{
    /**
     * Process the current expression.
     *
     * @return OrderedContainer|null An OrderedContainer object or NULL.
     * @throws OperatorProcessingException
     */
    public function process()
    {
        $operands = $this->getOperands();

        if (count($operands) === 0) {
            return null;
        }

        if ($operands->exclusivelySingleOrOrdered() === false) {
            $msg = "The Ordered operator only accepts operands with single or ordered cardinality.";
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
        }

        $refType = null;
        $returnValue = null;

        foreach ($operands as $operand) {
            if (is_null($operand) || ($operand instanceof OrderedContainer && $operand->isNull())) {
                // As per specs, ignore.
                continue;
            } else {
                if ($refType !== null) {
                    // A reference type as already been identifier.
                    if (CommonUtils::inferBaseType($operand) === $refType) {
                        // $operand can be added to $returnValue.
                        static::appendValue($returnValue, $operand);
                    } else {
                        // baseType mismatch.
                        $msg = "The Ordered operator only accepts values with a similar baseType.";
                        throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
                    }
                } elseif (($discoveryType = CommonUtils::inferBaseType($operand)) !== false) {
                    // First value being identified as non-null.
                    $refType = $discoveryType;
                    $returnValue = new OrderedContainer($refType);
                    static::appendValue($returnValue, $operand);
                }
            }
        }

        return $returnValue;
    }

    /**
     * Append a value (An orderedContainer or a primitive datatype) to a given $container.
     *
     * @param OrderedContainer $container An OrderedContainer object you want to append something to.
     * @param QtiScalar|OrderedContainer $value A value to append to the $container.
     */
    protected static function appendValue(OrderedContainer $container, $value)
    {
        if ($value instanceof OrderedContainer) {
            foreach ($value as $v) {
                $container[] = $v;
            }
        } else {
            // primitive type.
            $container[] = $value;
        }
    }

    /**
     * @see \qtism\runtime\expressions\ExpressionProcessor::getExpressionType()
     */
    protected function getExpressionType()
    {
        return Ordered::class;
    }
}
