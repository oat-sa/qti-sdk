<?php

namespace qtism\runtime\expressions;

use qtism\data\expressions\NumberCorrect;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The NumberCorrectProcessor aims at processing NumberCorrect
 * Outcome Processing only expressions.
 * 
 * From IMS QTI:
 * 
 * This expression, which can only be used in outcomes processing, calculates the number of 
 * items in a given sub-set, for which the all defined response variables match their 
 * associated correctResponse. Only items for which all declared response variables have 
 * correct responses defined are considered. The result is an integer with single cardinality.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NumberCorrectProcessor extends ItemSubsetProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof NumberCorrect) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The NumberCorrectProcessor class only accepts NumberCorrect expressions to be processed.";
			throw new InvalidArgumentException($expression);
		}
	}
	
	/**
	 * Process the related NumberCorrect expression.
	 * 
	 * @return integer The number of items of the given sub-set for which all the response variables match their associated correct response.
	 * @throws ExpressionProcessingException
	 */
	public function process() {
	    $testSession = $this->getState();
	    $itemSubset = $this->getItemSubset();
	    $numberCorrect = 0;
	    
	    foreach ($itemSubset as $item) {
	        $itemSessions = $testSession->getAssessmentItemSessions($item->getIdentifier());
	        
	        foreach ($itemSessions as $itemSession) {
	            if ($itemSession->isCorrect() === true) {
	                $numberCorrect++;
	            }
	        }
	    }
	    
	    return $numberCorrect;
	}
}