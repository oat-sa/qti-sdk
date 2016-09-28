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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 *
 */
namespace qtism\runtime\expressions;

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\runtime\common\MultipleContainer;
use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use qtism\data\expressions\TestVariables;
use qtism\data\expressions\Expression;

/**
 * The TestVariablesProcessor aims at processing TestVariables
 * Outcome Processing only expressions.
 *
 * From IMS QTI:
 *
 * This expression, which can only be used in outcomes processing, simultaneously looks up
 * the value of an itemVariable in a sub-set of the items referred to in a test. Only
 * variables with single cardinality are considered, all NULL values are ignored. The result
 * has cardinality multiple and base-type as specified below.
 *
 * The identifier of the variable to look up in each item. If a test brings together items
 * with different variable naming conventions variableMappings may be used to reduce the
 * complexity of outcomes processing and allow a single testVariables expression to be used.
 * Items with no matching variable are ignored.
 *
 * If specified, matches only variables declared with this baseType. This also becomes the
 * base-type of the result (subject to type promotion through weighting, as described below).
 * If omitted, variables declared with base-type integer or float are matched. The base-type
 * of the result is integer if all matching values have base-type integer, otherwise it is
 * float and integer values are subject to type promotion.
 *
 * If specified, the defined weight is applied to each variable as described in the definition
 * of weightIdentifier for a single variable. The behaviour of this attribute is only defined
 * if the baseType attribute is float or omitted . When a weighting is specified the result
 * of the expression always has base-type float. Note that this option is incomptable with
 * baseType integer. This restriction ensures that the value of the baseType attribute
 * remains consistent with the resulting container type.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TestVariablesProcessor extends ItemSubsetProcessor
{
    /**
	 * Process the related TestVariables expression.
	 *
	 * @return \qtism\runtime\common\MultipleContainer
	 * @throws \qtism\runtime\expressions\ExpressionProcessingException
	 */
    public function process()
    {
        $testSession = $this->getState();
        $itemSubset = $this->getItemSubset();
        $baseTypes = $this->getExpression()->getBaseType();
        $variableIdentifier = $this->getExpression()->getVariableIdentifier();
        $weightIdentifier = $this->getExpression()->getWeightIdentifier();
        $values = array();
        $integerCount = 0;

        // Which baseTypes are we going to accept?
        if ($baseTypes === -1) {
            // baseType ommited. only integer and float values are matched.
            $baseTypes = array(BaseType::INTEGER, BaseType::FLOAT);
        } else {
            // The base type is provided by the TestVariable expression.
            $baseTypes = array($baseTypes);
        }

        foreach ($itemSubset as $item) {
            $itemSessions = $testSession->getAssessmentItemSessions($item->getIdentifier());

            if ($itemSessions !== false) {
                foreach ($itemSessions as $itemSession) {
                
                    // Variable mapping takes place.
                    $id = self::getMappedVariableIdentifier($itemSession->getAssessmentItem(), $variableIdentifier);
                    if ($id === false) {
                        // variable name conflict.
                        continue;
                    }
                
                    // For each variable of the item session matching $outcomeIdentifier...
                    foreach ($itemSession->getKeys() as $identifier) {
                
                        $itemRefIdentifier = $itemSession->getAssessmentItem()->getIdentifier();
                        
                        if ($identifier === $id) {
                            $var = $itemSession->getVariable($id);
                            $weight = (empty($weightIdentifier) === true) ? false : $testSession->getWeight("${itemRefIdentifier}.${weightIdentifier}");
                
                            // Single cardinality? Does it match the baseType?
                            if ($var->getCardinality() === Cardinality::SINGLE && in_array($var->getBaseType(), $baseTypes) === true && $var->getValue() !== null) {
                                $val = clone($var->getValue());
                
                                if ($weight !== false && in_array(BaseType::FLOAT, $baseTypes) === true && ($val instanceof QtiInteger || $val instanceof QtiFloat)) {
                                    // A weight has to be applied.
                                    $val->setValue($val->getValue() * $weight->getValue());
                                }
                
                                $values[] = $val;
                
                                if (gettype($val->getValue()) === 'integer') {
                                    $integerCount++;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (count($baseTypes) > 1) {
            // baseType was ommited.
            if ($integerCount === count($values)) {
                // integer only in $results.
                $result = new MultipleContainer(BaseType::INTEGER, $values);
            } else {
                // integer + float values in $results.
                $result = new MultipleContainer(BaseType::FLOAT);

                // values are subject to type promotion.
                foreach ($values as $v) {
                    $result[] = new QtiFloat(floatval($v->getValue()));
                }
            }
        } else {
            // baseType was specified.
            $result = new MultipleContainer($baseTypes[0], $values);
        }

        return $result;
    }
    
    /**
     * @see \qtism\runtime\expressions\ExpressionProcessor::getExpressionType()
     */
    protected function getExpressionType()
    {
        return 'qtism\\data\\expressions\\TestVariables';
    }
}
