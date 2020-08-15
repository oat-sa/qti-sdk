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
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\expressions\ExpressionEngine;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * From IMS QTI:
 *
 * The response variable to have its correct value set.
 *
 */
class SetCorrectResponseProcessor extends RuleProcessor
{
    /**
     * Apply the current SetCorrectResponse rule on the current state.
     *
     * A RuleProcessingException will be thrown if:
     *
     * * No variable corresponds to the given identifier in the current state.
     * * The target variable is not a ResponseVariable.
     * * The baseType and/or cardinality of the value to be set does not correspond to the baseType and/or cardinality of the target variable.
     * * An error occurs while processing the expression representing the value to be set.
     *
     * @throws RuleProcessingException
     */
    public function process()
    {
        $rule = $this->getRule();
        $state = $this->getState();
        $variableIdentifier = $rule->getIdentifier();

        $var = $state->getVariable($variableIdentifier);

        if (is_null($var) === true) {
            $msg = "No variable with identifier '${variableIdentifier}' to be set in the current state.";
            throw new RuleProcessingException($msg, $this, RuleProcessingException::NONEXISTENT_VARIABLE);
        } elseif (!$var instanceof ResponseVariable) {
            $msg = "The variable to set '${variableIdentifier}' is not an instance of 'ResponseVariable'.";
            throw new RuleProcessingException($msg, $this, RuleProcessingException::WRONG_VARIABLE_TYPE);
        }

        try {
            $expressionEngine = new ExpressionEngine($rule->getExpression(), $state);
            $val = $expressionEngine->process();
            $var->setCorrectResponse($val);
        } catch (InvalidArgumentException $e) {
            $varBaseType = (BaseType::getNameByConstant($var->getBaseType()) === false) ? 'noBaseType' : BaseType::getNameByConstant($var->getBaseType());
            $varCardinality = (Cardinality::getNameByConstant($var->getCardinality()));
            // The affected value does not match the baseType of the variable $var.
            $msg = "Unable to set value ${val} to variable '${variableIdentifier}' (cardinality = ${varCardinality}, baseType = ${varBaseType}).";
            throw new RuleProcessingException($msg, $this, RuleProcessingException::WRONG_VARIABLE_BASETYPE, $e);
        } catch (ExpressionProcessingException $e) {
            $msg = "An error occurred while processing the expression bound with the 'SetCorrectResponse' rule.";
            throw new RuleProcessingException($msg, $this, RuleProcessingException::RUNTIME_ERROR, $e);
        }
    }

    /**
     * @see \qtism\runtime\rules\RuleProcessor::getRuleType()
     */
    protected function getRuleType()
    {
        return 'qtism\\data\\rules\\SetCorrectResponse';
    }
}
