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
 * Copyright (c) 2013-2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\runtime\expressions;

use qtism\common\enums\BaseType;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiFloat;
use qtism\common\Comparable;
use qtism\runtime\common\ResponseVariable;
use qtism\data\expressions\Expression;
use qtism\data\expressions\MapResponse;

/**
 * The MapResponseProcessor class aims at processing MapResponse Expression objects.
 *
 * FROM IMS QTI:
 *
 * This expression looks up the value of a response variable and then transforms it using the
 * associated mapping, which must have been declared. The result is a single float. If the
 * response variable has single cardinality then the value returned is simply the mapped
 * target value from the map. If the response variable has multiple or ordered cardinality
 * then the value returned is the sum of the mapped target values. This expression cannot
 * be applied to variables of record cardinality.
 *
 * For example, if a mapping associates the identifiers {A,B,C,D} with the values {0,1,0.5,0}
 * respectively then mapResponse will map the single value 'C' to the numeric value 0.5 and
 * the set of values {C,B} to the value 1.5.
 *
 * If a container contains multiple instances of the same value then that value is counted
 * once only. To continue the example above {B,B,C} would still map to 1.5 and not 2.5.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MapResponseProcessor extends ExpressionProcessor
{
    /**
	 * Process the MapResponse expression.
	 *
	 * * An ExpressionProcessingException is thrown if the variable is not defined.
	 * * An ExpressionProcessingException is thrown if the variable has no mapping defined.
	 * * An ExpressionProcessingException is thrown if the variable is not a ResponseVariable.
	 * * An ExpressionProcessingException is thrown if the cardinality of the variable is RECORD.
	 *
	 * @return a QTI float value.
	 * @throws \qtism\runtime\expressions\ExpressionProcessingException
	 */
    public function process()
    {
        $expr = $this->getExpression();
        $state = $this->getState();
        $identifier = $expr->getIdentifier();
        $variable = $state->getVariable($identifier);

        if (!is_null($variable)) {

            if ($variable instanceof ResponseVariable) {

                $mapping = $variable->getMapping();

                if (is_null($mapping)) {
                    return new QtiFloat(0.0);
                }

                // Single cardinality behaviour.
                if ($variable->isSingle()) {

                    $result = 0.0;
                    $mappedCount = 0;
                    foreach ($mapping->getMapEntries() as $mapEntry) {

                        $val = $state[$identifier];
                        $mapKey = $mapEntry->getMapKey();

                        if ($val instanceof QtiString && $mapEntry->isCaseSensitive() === false) {
                            $val = mb_strtolower($val->getValue(), 'UTF-8');
                            $mapKey = mb_strtolower($mapKey, 'UTF-8');
                        }
                        
                        if ($val instanceof Comparable && $val->equals($mapKey) || $val === $mapKey) {
                            $result += $mapEntry->getMappedValue();
                            $mappedCount++;
                        } elseif ($variable->getBaseType() === BaseType::STRING && $val === null && $mapKey === '') {
                            $result += $mapEntry->getMappedValue();
                            $mappedCount++;
                        }
                    }

                    if ($mappedCount === 0) {
                        // No relevant mapping found, return mapping default.
                        $result = $mapping->getDefaultValue();
                    }

                    // Always compare the result with lower or upper bound.
                    if ($mapping->hasLowerBound() && $result < $mapping->getLowerBound()) {
                        return new QtiFloat($mapping->getLowerBound());
                    } elseif ($mapping->hasUpperBound() && $result > $mapping->getUpperBound()) {
                        return new QtiFloat($mapping->getUpperBound());
                    } else {
                        return new QtiFloat($result);
                    }
                    
                // Multiple cardinality behaviour.
                } elseif ($variable->isMultiple()) {
                    
                    // Make the values in the collection unique values.
                    // See QTI 2.1 Spec (mapResponse) expression: "If a container contains multiple instances of the same 
                    // value then that value is counted once only".

                    $result = 0.0;
                    $variableValue = (count($variable->getValue()) === 0) ? array(null) : $variable->getValue()->distinct();
                    $mapEntries = $mapping->getMapEntries();

                    foreach ($variableValue as $val) {
                        $mappedCount = 0;

                        for ($i = 0; $i < count($mapEntries); $i++) {

                            $mapKey = $rawMapKey = $mapEntries[$i]->getMapKey();
                            if ($val instanceof QtiString && $mapEntries[$i]->isCaseSensitive() === false) {
                                $val = new QtiString(mb_strtolower($val->getValue(), 'UTF-8'));
                                $mapKey = mb_strtolower($mapKey, 'UTF-8');
                            }

                            if (($val instanceof Comparable && $val->equals($mapKey) === true) || ($variable->getBaseType() === BaseType::STRING && $val === null && $mapKey === '')) {
                                $result += $mapEntries[$i]->getMappedValue();
                                $mappedCount++;
                            }
                        }

                        if ($mappedCount === 0) {
                            // No explicit mapping found for source value $val.
                            $result += $mapping->getDefaultValue();
                        }
                    }

                    // When mapping a container, try to apply lower or upper bound.
                    if ($mapping->hasLowerBound() && $result < $mapping->getLowerBound()) {
                        return new QtiFloat($mapping->getLowerBound());
                    } elseif ($mapping->hasUpperBound() && $result > $mapping->getUpperBound()) {
                        return new QtiFloat($mapping->getUpperBound());
                    } else {
                        return new QtiFloat($result);
                    }
                } else {
                    $msg = "MapResponse cannot be applied on a Record container.";
                    throw new ExpressionProcessingException($msg, $this, ExpressionProcessingException::WRONG_VARIABLE_BASETYPE);
                }
                
            } else {
                $msg = "The target variable of a MapResponse expression must be a ResponseVariable.";
                throw new ExpressionProcessingException($msg, $this, ExpressionProcessingException::WRONG_VARIABLE_TYPE);
            }
        } else {
            $msg = "No variable with identifier '${identifier}' could be found while processing MapResponse.";
            throw new ExpressionProcessingException($msg, $this, ExpressionProcessingException::NONEXISTENT_VARIABLE);
        }
    }
    
    /**
     * @see \qtism\runtime\expressions\ExpressionProcessor::getExpressionType()
     */
    protected function getExpressionType()
    {
        return 'qtism\\data\\expressions\\MapResponse';
    }
}
