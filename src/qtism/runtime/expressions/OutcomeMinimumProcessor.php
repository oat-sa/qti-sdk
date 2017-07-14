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
use qtism\common\enums\BaseType;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\MultipleContainer;
use qtism\data\expressions\OutcomeMinimum;
use qtism\data\expressions\Expression;

/**
 * The OutcomeMinimumProcessor aims at processing OutcomeMinimum
 * Outcome Processing only expressions.
 *
 * From IMS QTI:
 *
 * This expression, which can only be used in outcomes processing, simultaneously looks up
 * the normalMinimum value of an outcome variable in a sub-set of the items referred to in a
 * test. Only variables with single cardinality are considered. Items with no declared
 * minimum are ignored. The result has cardinality multiple and base-type float.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeMinimumProcessor extends ItemSubsetProcessor
{
    /**
	 * Process the related OutcomeMinimum expression.
	 *
	 * @return \qtism\runtime\common\MultipleContainer|null A MultipleContainer object with baseType float containing all the retrieved normalMinimum values or NULL if no declared minimum in the sub-set.
	 * @throws \qtism\runtime\expressions\ExpressionProcessingException
	 */
    public function process()
    {
        $itemSubset = $this->getItemSubset();
        $testSession = $this->getState();
        $outcomeIdentifier = $this->getExpression()->getOutcomeIdentifier();
        // If no weightIdentifier specified, its value is an empty string ('').
        $weightIdentifier = $this->getExpression()->getWeightIdentifier();
        $result = new MultipleContainer(BaseType::FLOAT);

        foreach ($itemSubset as $item) {
            $itemSessions = $testSession->getAssessmentItemSessions($item->getIdentifier());

            foreach ($itemSessions as $itemSession) {

               // Variable mapping is in force.
               $id = self::getMappedVariableIdentifier($itemSession->getAssessmentItem(), $outcomeIdentifier);
               if ($id === false) {
                   // Variable name conflict.
                   continue;
               }

               if (isset($itemSession[$id]) && $itemSession->getVariable($id) instanceof OutcomeVariable) {

                    $var = $itemSession->getVariable($id);
                    $itemRefIdentifier = $itemSession->getAssessmentItem()->getIdentifier();
                    $weight = (empty($weightIdentifier) === true) ? false : $testSession->getWeight("${itemRefIdentifier}.${weightIdentifier}");

                    // Does this OutcomeVariable contain a value for normalMaximum?
                    if (($normalMinimum = $var->getNormalMinimum()) !== false) {
                        if ($weight === false) {
                            // No weight to be applied.
                            $result[] = new QtiFloat($normalMinimum);
                        } else {

                            // A weight has to be applied.
                            $result[] = new QtiFloat(floatval($normalMinimum *= $weight->getValue()));
                        }
                    }
                    // else ... items with no declared minimum are ignored.
                }
            }
        }

        return $result;
    }
    
    /**
     * @see \qtism\runtime\expressions\ExpressionProcessor::getExpressionType()
     */
    protected function getExpressionType()
    {
        return 'qtism\\data\\expressions\\OutcomeMinimum';
    }
}
