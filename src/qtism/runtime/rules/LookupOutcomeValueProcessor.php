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
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\data\rules\LookupOutcomeValue;
use qtism\data\state\InterpolationTable;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\Utils as RuntimeUtils;
use qtism\runtime\expressions\ExpressionEngine;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * From IMS QTI:
 *
 * The lookupOutcomeValue rule sets the value of an outcome variable to the value
 * obtained by looking up the value of the associated expression in the lookupTable
 * associated with the outcome's declaration.
 */
class LookupOutcomeValueProcessor extends RuleProcessor
{
    /**
     * Process the LookupOutcomeValue rule.
     *
     * A RuleProcessingException will be thrown if:
     *
     * * The outcome variable to set does not exist.
     * * The variable to set is not an OutcomeVariable
     * * The outcome variable's baseType does not match the baseType of the affected value (the result of the bound expression).
     * * The outcome variable's declaration has no associated lookup table.
     * * The variable's declaration contains a matchTable but the result of the bound expression is not an integer.
     * * The variable's declaration contains an interpolationTable but the result of the bound expression is not an integer, nor a float.
     * * There is no associated table in the variable's declaration.
     * * An error occurs during the processing of the related expression.
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
            $msg = "The variable to set '${identifier}' does not exist in the current state.";
            throw new RuleProcessingException($msg, $this, RuleProcessingException::NONEXISTENT_VARIABLE);
        } elseif (!$var instanceof OutcomeVariable) {
            $msg = "The variable to set '${identifier}' is not an OutcomeVariable.";
            throw new RuleProcessingException($msg, $this, RuleProcessingException::WRONG_VARIABLE_TYPE);
        }

        $expression = $rule->getExpression();
        $expressionEngine = new ExpressionEngine($expression, $state);

        try {
            $val = $expressionEngine->process();

            // Let's lookup the associated table.
            $table = $var->getLookupTable();
            if ($table === null) {
                $msg = "No lookupTable in declaration of variable '${identifier}'.";
                throw new RuleProcessingException($msg, $this, RuleProcessingException::LOGIC_ERROR);
            }

            // $targetVal = The value that will be set to the target variable.
            //
            // As per specs:

            // The default outcome value to be used when no matching table
            // entry is found. If omitted, the NULL value is used.
            $targetVal = $table->getDefaultValue();

            if ($table instanceof InterpolationTable) {
                if (!$val instanceof QtiFloat && !$val instanceof QtiInteger && !$val instanceof QtiDuration) {
                    $msg = "The value of variable '${identifier}' must be integer, float or duration when used with an interpolationTable";
                    throw new RuleProcessingException($msg, $this, RuleProcessingException::LOGIC_ERROR);
                }

                foreach ($table->getInterpolationTableEntries() as $entry) {
                    $lowerBound = $entry->getSourceValue();
                    $includeBoundary = $entry->doesIncludeBoundary();

                    if ($includeBoundary === true && $val->getValue() <= $lowerBound) {
                        $targetVal = $entry->getTargetValue();
                        break;
                    } elseif ($includeBoundary === false && $val->getValue() < $lowerBound) {
                        $targetVal = $entry->getTargetValue();
                        break;
                    }
                }
            } else {
                // $table instanceof MatchTable
                if (!$val instanceof QtiInteger) {
                    $msg = "The value of the variable '${identifier}' must be integer when used with a matchTable.";
                    throw new RuleProcessingException($msg, $this, RuleProcessingException::LOGIC_ERROR);
                }

                foreach ($table->getMatchTableEntries() as $entry) {
                    if ($entry->getSourceValue() === $val->getValue()) {
                        $targetVal = $entry->getTargetValue();
                        break;
                    }
                }
            }

            // assign target value
            try {
                $finalVal = RuntimeUtils::valueToRuntime($targetVal, $var->getBaseType());
                $state[$identifier] = $finalVal;
            } catch (InvalidArgumentException $e) {
                // $targetVal's baseType not compliant with target variable's baseType.
                $msg = "The looked up value's baseType is not compliant with the baseType of variable '${identifier}'.";
                throw new RuleProcessingException($msg, $this, RuleProcessingException::RUNTIME_ERROR);
            }
        } catch (ExpressionProcessingException $e) {
            $msg = 'An error occurred while processing the expression bound to the lookupOutcomeValue rule.';
            throw new RuleProcessingException($msg, $this, RuleProcessingException::RUNTIME_ERROR, $e);
        }
    }

    /**
     * @return string
     */
    protected function getRuleType()
    {
        return LookupOutcomeValue::class;
    }
}
