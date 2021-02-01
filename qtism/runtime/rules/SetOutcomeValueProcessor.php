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

namespace qtism\runtime\rules;

use InvalidArgumentException;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\rules\Rule;
use qtism\data\rules\SetOutcomeValue;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\expressions\ExpressionEngine;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * From IMS QTI:
 *
 * The setOutcomeValue rule sets the value of an outcome variable to the value
 * obtained from the associated expression. An outcome variable can be updated with
 * reference to a previously assigned value, in other words, the outcome variable
 * being set may appear in the expression where it takes the value previously assigned
 * to it.
 *
 * Special care is required when using the numeric base-types because floating point
 * values can not be assigned to integer variables and vice-versa. The truncate,
 * round or integerToFloat operators must be used to achieve numeric type conversion.
 */
class SetOutcomeValueProcessor extends RuleProcessor
{
    /**
     * Process the setOutcomeValue rule.
     *
     * A RuleProcessingException will be thrown if:
     *
     * * The outcome variable does not exist.
     * * The requested variable is not an OutcomeVariable.
     * * The outcome variable's baseType does not match the baseType of the affected value.
     * * An error occurs while processing the related expression.
     *
     * @throws RuleProcessingException If one of the error described above arise.
     */
    public function process()
    {
        $state = $this->getState();
        $rule = $this->getRule();
        $identifier = $rule->getIdentifier();
        $var = $state->getVariable($identifier);

        if ($var === null) {
            $msg = "No variable with identifier '${identifier}' to be set in the current state.";
            throw new RuleProcessingException($msg, $this, RuleProcessingException::NONEXISTENT_VARIABLE);
        } elseif (!$var instanceof OutcomeVariable) {
            $msg = "The variable to set '${identifier}' is not an OutcomeVariable.";
            throw new RuleProcessingException($msg, $this, RuleProcessingException::WRONG_VARIABLE_TYPE);
        }

        // Process the expression.
        // Its result will be the value to set to the target variable.
        try {
            $expressionEngine = new ExpressionEngine($rule->getExpression(), $state);
            $val = $expressionEngine->process();
        } catch (ExpressionProcessingException $e) {
            $msg = 'An error occurred while processing the expression bound with the setOutcomeValue rule.';
            throw new RuleProcessingException($msg, $this, RuleProcessingException::RUNTIME_ERROR, $e);
        }

        // The variable exists, its new value is processed.
        try {
            // juggle a little bit (int -> float, float -> int)
            if ($val !== null && $var->getCardinality() === Cardinality::SINGLE) {
                $baseType = $var->getBaseType();

                // If we are trying to put a container in a scalar, let's make some changes...
                if (($val->getCardinality() === Cardinality::MULTIPLE || $val->getCardinality() === Cardinality::ORDERED) && count($val) > 0) {
                    $val = $val[0];
                }

                if ($baseType === BaseType::INTEGER && $val instanceof QtiFloat) {
                    $val = new QtiInteger((int)$val->getValue());
                } elseif ($baseType === BaseType::FLOAT && $val instanceof QtiInteger) {
                    $val = new QtiFloat((float)$val->getValue());
                }
            }

            $var->setValue($val);
        } catch (InvalidArgumentException $e) {
            $varBaseType = (BaseType::getNameByConstant($var->getBaseType()) === false) ? 'noBaseType' : BaseType::getNameByConstant($var->getBaseType());
            $varCardinality = (Cardinality::getNameByConstant($var->getCardinality()));
            // The affected value does not match the baseType of the variable $var.
            $msg = "Unable to set value ${val} to variable '${identifier}' (cardinality = ${varCardinality}, baseType = ${varBaseType}).";
            throw new RuleProcessingException($msg, $this, RuleProcessingException::WRONG_VARIABLE_BASETYPE, $e);
        }
    }

    /**
     * @return string
     */
    protected function getRuleType()
    {
        return SetOutcomeValue::class;
    }
}
