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
use qtism\common\datatypes\QtiInteger;
use qtism\data\expressions\operators\AnyN;
use qtism\runtime\expressions\Utils;

/**
 * The AnyNProcessor class aims at processing AnyN expressions.
 *
 * From IMS QTI:
 *
 * The anyN operator takes one or more sub-expressions each with a base-type of
 * boolean and single cardinality. The result is a single boolean which is true
 * if at least min of the sub-expressions are true and at most max of the
 * sub-expressions are true. If more than n - min sub-expressions are false
 * (where n is the total number of sub-expressions) or more than max sub-expressions
 * are true then the result is false. If one or more sub-expressions are NULL then
 * it is possible that neither of these conditions is satisfied, in which case
 * the operator results in NULL. For example, if min is 3 and max is 4 and the
 * sub-expressions have values {true,true,false,NULL} then the operator results in
 * NULL whereas {true,false,false,NULL} results in false and {true,true,true,NULL}
 * results in true. The result NULL indicates that the correct value for the operator
 * cannot be determined.
 */
class AnyNProcessor extends OperatorProcessor
{
    /**
     * Process the AnyN processor.
     *
     * @return QtiBoolean|null A boolean value of true if at least min of the sub-expressions are true and at most max of the sub-expressions are true. NULL is returned if the correct value for the operator cannot be determined.
     * @throws OperatorProcessingException
     */
    public function process(): ?QtiBoolean
    {
        $operands = $this->getOperands();

        // Retrieve the values of min and max.
        $min = $this->getExpression()->getMin();
        $max = $this->getExpression()->getMax();

        // @todo write a generic method to retrieve variable references.

        if (is_string($min)) {
            // variable reference for 'min' to handle.
            $state = $this->getState();
            $varName = Utils::sanitizeVariableRef($min);
            $varValue = $state[$varName];

            if ($varValue === null) {
                $msg = "The variable with name '{$varName}' could not be resolved or is null.";
                throw new OperatorProcessingException($msg, $this, OperatorProcessingException::NONEXISTENT_VARIABLE);
            } elseif (!$varValue instanceof QtiInteger) {
                $msg = "The variable with name '{$varName}' is not an integer.";
                throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
            } else {
                $min = $varValue->getValue();
            }
        }

        if (is_string($max)) {
            // variable reference for 'max' to handle.
            $state = $this->getState();
            $varName = Utils::sanitizeVariableRef($max);
            $varValue = $state[$varName];

            if ($varValue === null) {
                $msg = "The variable with name '{$varName}' could not be resolved or is null.";
                throw new OperatorProcessingException($msg, $this, OperatorProcessingException::NONEXISTENT_VARIABLE);
            } elseif (!$varValue instanceof QtiInteger) {
                $msg = "The variable with name '{$varName}' is not an integer.";
                throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_VARIABLE_BASETYPE);
            } else {
                $max = $varValue->getValue();
            }
        }

        $nullCount = 0;
        $trueCount = 0;

        foreach ($operands as $operand) {
            if ($operand === null) {
                $nullCount++;
                continue;
            } elseif ($operand instanceof QtiBoolean) {
                if ($operand->getValue() === true) {
                    $trueCount++;
                }
            } else {
                // Not null, not a boolean, we have a problem...
                $msg = 'The AnyN operator only accepts values with cardinality single and baseType boolean.';
                throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE_OR_CARDINALITY);
            }
        }

        if ($trueCount >= $min && $trueCount <= $max) {
            return new QtiBoolean(true);
        } elseif ($trueCount + $nullCount >= $min && $trueCount + $nullCount <= $max) {
            // It could have match if nulls were true values.
            return null;
        } else {
            return new QtiBoolean(false);
        }
    }

    /**
     * @return string
     */
    protected function getExpressionType(): string
    {
        return AnyN::class;
    }
}
