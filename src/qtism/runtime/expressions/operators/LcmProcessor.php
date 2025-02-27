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

use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiScalar;
use qtism\data\expressions\operators\Lcm;

/**
 * The LcmProcessor class aims at processing Lcm operators.
 *
 * From IMS QTI:
 *
 * The lcm operator takes 1 or more sub-expressions which all have base-type integer and
 * may have single, multiple or ordered cardinality. The result is a single integer equal
 * in value to the lowest common multiple (lcm) of the argument values. If any argument
 * is zero, the result is 0, lcm(0,n)=0; authors should beware of this in calculations
 * which require division by the lcm of random values. If any of the sub-expressions is
 * NULL, the result is NULL. If any of the sub-expressions is not a numerical value,
 * then the result is NULL.
 */
class LcmProcessor extends OperatorProcessor
{
    /**
     * Process the Lcm operator.
     *
     * @return QtiInteger|null A single integer equal in value to the lowest common multiple of the sub-expressions. If all arguments are 0, the result is 0, If any of the sub-expressions is NULL, the result is NULL.
     * @throws OperatorProcessingException
     */
    public function process(): ?QtiInteger
    {
        $operands = $this->getOperands();

        if ($operands->containsNull() === true) {
            return null;
        }

        if ($operands->anythingButRecord() === false) {
            $msg = 'The Lcm operator only accepts operands with a cardinality of single, multiple or ordered.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
        }

        if ($operands->exclusivelyInteger() === false) {
            $msg = 'The Lcm operator only accepts operands with an integer baseType.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
        }

        // Make a flat collection first.
        $flatCollection = new OperandsCollection();

        foreach ($operands as $operand) {
            if ($operand instanceof QtiScalar) {
                if ($operand->getValue() !== 0) {
                    $flatCollection[] = $operand;
                } else {
                    // Operand is 0, return 0.
                    return new QtiInteger(0);
                }
            } elseif ($operand->contains(null)) {
                // Container with at least one null value inside.
                // -> If any of the sub-expressions is null or not numeric, returns null.
                return null;
            } else {
                // Container with no null values.
                foreach ($operand as $o) {
                    if ($o->getValue() !== 0) {
                        $flatCollection[] = $o;
                    } else {
                        // If any of the operand is 0, return 0.
                        return new QtiInteger(0);
                    }
                }
            }
        }

        $g = $flatCollection[0];
        $loopLimit = count($flatCollection) - 1;
        $i = 0;

        while ($i < $loopLimit) {
            $g = new QtiInteger(Utils::lcm($g->getValue(), $flatCollection[$i + 1]->getValue()));
            $i++;
        }

        return $g;
    }

    /**
     * @return string
     */
    protected function getExpressionType(): string
    {
        return Lcm::class;
    }
}
