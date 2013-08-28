<?php

namespace qtism\runtime\expressions;

use qtism\common\enums\BaseType;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\MultipleContainer;
use qtism\data\expressions\OutcomeMaximum;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The OutcomeMaximumProcessor aims at processing OutcomeMaximum
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
class OutcomeMaximumProcessor extends ItemSubsetProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof OutcomeMaximum) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The OutcomeMaximumProcessor class only accepts OutcomeMaximum expressions to be processed.";
			throw new InvalidArgumentException($expression);
		}
	}
	
	/**
	 * Process the related OutcomeMaximum expression.
	 * 
	 * @return MultipleContainer|null A MultipleContainer object with baseType float containing all the retrieved normalMaximum values or NULL if no declared maximum in the sub-set. 
	 * @throws ExpressionProcessingException
	 */
	public function process() {
	    $itemSubset = $this->getItemSubset();
	    
	    if (count($itemSubset) === 0) {
	        return null;
	    }
	    
	    $testSession = $this->getState();
	    $outcomeIdentifier = $this->getExpression()->getOutcomeIdentifier();
	    // If no weightIdentifier specified, its value is an empty string ('').
	    $weightIdentifier = $this->getExpression()->getWeightIdentifier();
	    $weight = (empty($weightIdentifier) === true) ? false : $testSession->getWeight($weightIdentifier);
	    $result = new MultipleContainer(BaseType::FLOAT);
	    
	    foreach ($itemSubset as $item) {
	        $itemSessions = $testSession->getAssessmentItemSessions($item->getIdentifier());
	        
	        foreach ($itemSessions as $itemSession) {
	            
	           // Apply variable mapping on $outcomeIdentifier.
	           $outcomeIdentifier = self::getMappedVariableIdentifier($itemSession->getAssessmentItemRef(), $outcomeIdentifier);
	            
	           if (isset($itemSession[$outcomeIdentifier]) && $itemSession->getVariable($outcomeIdentifier) instanceof OutcomeVariable) {
	                
	                $var = $itemSession->getVariable($outcomeIdentifier);
	                    
                    // Does this OutcomeVariable contain a value for normalMaximum?
                    if (($normalMaximum = $var->getNormalMaximum()) !== false) {
                        
                        if ($weight === false) {
                            // No weight to be applied.
                            $result[] = $normalMaximum;
                        }
                        else {
                            // A weight has to be applied.
                            $result[] = floatval($normalMaximum *= $weight->getValue());
                        }
                    }
                    else {
                        // If any of the items in the given subset have no declared maximum
                        // the result is NULL.
                        return null;
                    }
	            }
	        }
	    }
	    
	    return $result;
	}
}