<?php

namespace qtism\runtime\expressions;

use qtism\common\enums\BaseType;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\MultipleContainer;
use qtism\data\expressions\OutcomeMinimum;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

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
class OutcomeMinimumProcessor extends ItemSubsetProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof OutcomeMinimum) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The OutcomeMinimumProcessor class only accepts OutcomeMinimum expressions to be processed.";
			throw new InvalidArgumentException($expression);
		}
	}
	
	/**
	 * Process the related OutcomeMinimum expression.
	 * 
	 * @return MultipleContainer|null A MultipleContainer object with baseType float containing all the retrieved normalMinimum values or NULL if no declared minimum in the sub-set. 
	 * @throws ExpressionProcessingException
	 */
	public function process() {
	    $itemSubset = $this->getItemSubset();
	    $testSession = $this->getState();
	    $outcomeIdentifier = $this->getExpression()->getOutcomeIdentifier();
	    // If no weightIdentifier specified, its value is an empty string ('').
	    $weightIdentifier = $this->getExpression()->getWeightIdentifier();
	    $weight = (empty($weightIdentifier) === true) ? false : $testSession->getWeight($weightIdentifier);
	    $result = new MultipleContainer(BaseType::FLOAT);
	    
	    foreach ($itemSubset as $item) {
	        $itemSessions = $testSession->getAssessmentItemSessions($item->getIdentifier());
	        
	        foreach ($itemSessions as $itemSession) {
	            
	           // Variable mapping is in force.
	           $id = self::getMappedVariableIdentifier($itemSession->getAssessmentItemRef(), $outcomeIdentifier); 
	           if ($id === false) {
	               // Variable name conflict.
	               continue;
	           }
	           
	           if (isset($itemSession[$id]) && $itemSession->getVariable($id) instanceof OutcomeVariable) {
	               
	                $var = $itemSession->getVariable($id);
	                 
                    // Does this OutcomeVariable contain a value for normalMaximum?
                    if (($normalMinimum = $var->getNormalMinimum()) !== false) {
                        if ($weight === false) {
                            // No weight to be applied.
                            $result[] = $normalMinimum;
                        }
                        else {
                            
                            // A weight has to be applied.
                            $result[] = floatval($normalMinimum *= $weight->getValue());
                        }
                    }
                    // else ... items with no declared minimum are ignored.
	            }
	        }
	    }
	    
	    return $result;
	}
}